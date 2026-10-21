<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use App\Services\PaymentInsightsService;
use Illuminate\Console\Command;

class PaymentExpiryAlertsCommand extends Command
{
    protected $signature = 'payments:expiry-alerts';

    protected $description = 'إشعار الإدارة بالسدادات التي تنتهي خلال مدة التنبيه المحددة في الإعدادات';

    public function handle(PaymentInsightsService $insights, NotificationService $notifications): int
    {
        $days = $insights->alertDays();
        $total = 0;

        foreach (['trainee' => 'المتربصين', 'employee' => 'الموظفين'] as $type => $label) {
            $expiring = $insights->renewalCandidates($type)
                ->filter(fn ($payment) => $payment->daysLeft() >= 0 && $payment->expiry_notified_at === null);

            if ($expiring->isEmpty()) {
                continue;
            }

            $names = $expiring
                ->take(3)
                ->map(fn ($payment) => $type === 'employee'
                    ? $payment->employee?->full_name
                    : $payment->trainee?->full_name)
                ->filter()
                ->implode('، ');

            $extra = $expiring->count() > 3 ? ' و'.($expiring->count() - 3).' آخرين' : '';

            $notifications->notify(
                [User::ROLE_ADMIN, User::ROLE_SUPERVISOR],
                'payment.expiring',
                'سدادات '.$label.' تنتهي خلال '.$days.' أيام',
                $expiring->count().' سدادًا — '.$names.$extra,
                'warning',
                ['type' => $type, 'count' => $expiring->count()]
            );

            foreach ($expiring as $payment) {
                $payment->update(['expiry_notified_at' => now()]);
            }

            $total += $expiring->count();
            $this->info('سدادات '.$label.': '.$expiring->count());
        }

        if ($total === 0) {
            $this->info('لا توجد سدادات تنتهي خلال '.$days.' أيام.');

            return self::SUCCESS;
        }

        $this->info('تم إرسال تنبيهات حول '.$total.' سدادًا.');

        return self::SUCCESS;
    }
}
