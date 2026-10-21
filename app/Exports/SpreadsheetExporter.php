<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * مُصدِّر موحّد لملفات CSV وXLSX مع دعم الاتجاه من اليمين إلى اليسار.
 */
class SpreadsheetExporter
{
    /**
     * تصدير صفوف إلى ملف CSV بترميز UTF-8 مع BOM، مع حماية من Formula Injection.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function csv(array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($handle, array_map(fn ($cell) => $this->sanitize((string) $cell), $row));
            }

            fclose($handle);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * تصدير صفوف إلى ملف XLSX بترميز UTF-8 واتجاه RTL وعناوين عريضة.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function xlsx(array $rows, string $filename, string $sheetTitle = 'تقرير'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($sheetTitle, 0, 30));
        $sheet->setRightToLeft(true);

        $rowIndex = 1;

        foreach ($rows as $row) {
            $colIndex = 1;

            foreach ($row as $cell) {
                $sheet->setCellValueExplicit(
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex).$rowIndex,
                    (string) $cell,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $colIndex++;
            }

            $rowIndex++;
        }

        if ($rowIndex > 1) {
            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(max(1, count($rows[0] ?? [1])));

            $sheet->getStyle('A1:'.$lastColumn.'1')->getFont()->setBold(true);
            $sheet->getStyle('A1:'.$lastColumn.'1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('EEF2F6');
            $sheet->getStyle('A1:'.$lastColumn.'1')->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            foreach (range(1, max(1, count($rows[0] ?? [1]))) as $col) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** حماية CSV من Formula Injection */
    private function sanitize(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
