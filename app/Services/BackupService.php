<?php

namespace App\Services;

use App\Models\BackupLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * إنشاء وإدارة النسخ الاحتياطية لقاعدة البيانات.
 *
 * يعتمد أولًا على أداة mysqldump إذا كانت متاحة، وإلا يستخدم نسخة PHP
 * مبنية على PDO لضمان عمل الميزة على الاستضافات المشتركة (cPanel).
 */
class BackupService
{
    /** مجلد النسخ الاحتياطية داخل القرص الخاص */
    public const DIR = 'backups';

    public function __construct(
        private SettingsService $settings
    ) {
    }

    /** إنشاء نسخة احتياطية وتسجيلها */
    public function create(?int $userId, ?string $note = null): BackupLog
    {
        $filename = 'zawia-'.now()->format('Ymd-His').'.sql';
        $relative = self::DIR.'/'.$filename;
        $absolute = $this->absolutePath($relative);

        try {
            $this->dump($absolute);
            $size = is_file($absolute) ? (int) filesize($absolute) : 0;

            if ($size <= 0) {
                throw new RuntimeException('ملف النسخة الاحتياطية فارغ.');
            }

            $log = BackupLog::create([
                'filename' => $filename,
                'size' => $size,
                'status' => 'success',
                'note' => $note,
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            $this->prune();

            return $log;
        } catch (\Throwable $e) {
            if (is_file($absolute)) {
                @unlink($absolute);
            }

            return BackupLog::create([
                'filename' => $filename,
                'size' => 0,
                'status' => 'failed',
                'note' => $e->getMessage(),
                'created_by' => $userId,
                'created_at' => now(),
            ]);
        }
    }

    public function exists(BackupLog $log): bool
    {
        return Storage::disk('local')->exists(self::DIR.'/'.basename($log->filename));
    }

    public function delete(BackupLog $log): void
    {
        Storage::disk('local')->delete(self::DIR.'/'.basename($log->filename));

        $log->delete();
    }

    /** الاحتفاظ بعدد محدد من النسخ الناجحة وحذف الأقدم */
    public function prune(): void
    {
        $retention = (int) $this->settings->number('backup_retention', 10);

        if ($retention < 1) {
            return;
        }

        BackupLog::query()
            ->where('status', 'success')
            ->latest('id')
            ->skip($retention)
            ->take(200)
            ->get()
            ->each(fn (BackupLog $log) => $this->delete($log));
    }

    /* ---------------------------------------------------------------
     |  التنفيذ
     | --------------------------------------------------------------- */

    private function dump(string $absolute): void
    {
        Storage::disk('local')->makeDirectory(self::DIR);

        $binary = $this->mysqldumpBinary();

        if ($binary !== null) {
            try {
                $this->dumpWithBinary($binary, $absolute);

                return;
            } catch (\Throwable) {
                // تعذّر تشغيل mysqldump (بيئة مقيدة): ننتقل إلى النسخة البديلة المبنية على PDO.
                if (is_file($absolute)) {
                    @unlink($absolute);
                }
            }
        }

        $this->dumpWithPhp($absolute);
    }

    /** البحث عن مسار mysqldump: متغير بيئة ثم مسارات شائعة */
    private function mysqldumpBinary(): ?string
    {
        $candidates = array_filter([
            env('BACKUP_MYSQLDUMP_PATH'),
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
        ]);

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function dumpWithBinary(string $binary, string $absolute): void
    {
        $config = config('database.connections.mysql');

        $command = [
            $binary,
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--single-transaction',
            '--skip-lock-tables',
            '--default-character-set=utf8mb4',
            '--routines',
            '--events',
            (string) $config['database'],
        ];

        $handle = fopen($absolute, 'wb');

        if ($handle === false) {
            throw new RuntimeException('تعذّر إنشاء ملف النسخة الاحتياطية.');
        }

        $process = new Process($command, null, ['MYSQL_PWD' => (string) $config['password']]);
        $process->setTimeout(600);

        $process->run(function (string $type, string $buffer) use ($handle) {
            if ($type === Process::OUT) {
                fwrite($handle, $buffer);
            }
        });

        fclose($handle);

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'فشل تنفيذ mysqldump.');
        }

        if (! str_contains((string) file_get_contents($absolute, false, null, 0, 2048), 'CREATE TABLE')) {
            throw new RuntimeException('مخرجات mysqldump غير صالحة.');
        }
    }

    /** نسخة احتياطية مبنية على PDO تعمل دون أدوات خارجية */
    private function dumpWithPhp(string $absolute): void
    {
        $handle = fopen($absolute, 'wb');

        if ($handle === false) {
            throw new RuntimeException('تعذّر إنشاء ملف النسخة الاحتياطية.');
        }

        $connection = DB::connection();
        $database = $connection->getDatabaseName();

        fwrite($handle, "-- نسخة احتياطية لمنصة الزاوية\n");
        fwrite($handle, '-- قاعدة البيانات: '.$database."\n");
        fwrite($handle, '-- التاريخ: '.now()->toDateTimeString()."\n\n");
        fwrite($handle, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = array_map(
            fn ($row) => (string) array_values((array) $row)[0],
            DB::select('SHOW FULL TABLES')
        );

        foreach ($tables as $table) {
            $createRow = (array) DB::selectOne('SHOW CREATE TABLE `'.$table.'`');
            $createSql = (string) array_values($createRow)[1];

            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n{$createSql};\n\n");

            $this->writeTableRows($handle, $table);
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");

        fclose($handle);
    }

    /** كتابة صفوف جدول على شكل عبارات INSERT مجمّعة */
    private function writeTableRows($handle, string $table): void
    {
        $columns = null;
        $batch = [];
        $prefix = '';

        foreach (DB::table($table)->cursor() as $row) {
            $row = (array) $row;

            if ($columns === null) {
                $columns = array_keys($row);
                $prefix = 'INSERT INTO `'.$table.'` (`'.implode('`,`', $columns).'`) VALUES ';
            }

            $batch[] = '('.implode(',', array_map(fn ($value) => $this->quote($value), array_values($row))).')';

            if (count($batch) >= 200) {
                fwrite($handle, $prefix.implode(',', $batch).";\n");
                $batch = [];
            }
        }

        if ($batch !== []) {
            fwrite($handle, $prefix.implode(',', $batch).";\n");
        }

        fwrite($handle, "\n");
    }

    private function quote(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return DB::connection()->getPdo()->quote((string) $value);
    }

    private function absolutePath(string $relative): string
    {
        return Storage::disk('local')->path($relative);
    }
}
