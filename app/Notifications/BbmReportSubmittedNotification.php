<?php

namespace App\Notifications;

use App\Models\BbmReport;
use Illuminate\Notifications\Notification;

class BbmReportSubmittedNotification extends Notification
{
    public function __construct(
        public BbmReport $bbmReport
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $this->bbmReport->loadMissing('user');
        $name = $this->bbmReport->user?->name
            ?? $this->bbmReport->user?->username
            ?? 'Driver';
        $liter = number_format((float) $this->bbmReport->liter, 2, ',', '.');

        return [
            'title' => 'Laporan pengisian BBM baru',
            'body' => "{$name} mengirim laporan BBM {$liter} L untuk {$this->bbmReport->nomor_kendaraan}.",
            'url' => route('admin.portal-bbm-operasional'),
            'bbm_report_id' => $this->bbmReport->id,
        ];
    }
}
