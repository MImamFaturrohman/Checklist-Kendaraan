<?php

namespace App\Support;

use App\Models\BbmReport;
use App\Models\Checklist;
use App\Models\User;
use App\Notifications\BbmReportSubmittedNotification;
use App\Notifications\ChecklistSubmittedNotification;
use Illuminate\Support\Facades\Auth;

final class SuperAdminNotifier
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private static function superadminRecipients(): \Illuminate\Database\Eloquent\Collection
    {
        $q = User::query()->where('role', 'superadmin');
        $uid = Auth::id();
        if ($uid !== null) {
            $q->where('id', '!=', $uid);
        }

        return $q->get();
    }

    public static function checklistSubmitted(Checklist $checklist): void
    {
        $notification = new ChecklistSubmittedNotification($checklist);
        self::superadminRecipients()->each(function (User $user) use ($notification): void {
            $user->notify($notification);
        });
    }

    public static function bbmReportSubmitted(BbmReport $report): void
    {
        $notification = new BbmReportSubmittedNotification($report);
        self::superadminRecipients()->each(function (User $user) use ($notification): void {
            $user->notify($notification);
        });
    }
}
