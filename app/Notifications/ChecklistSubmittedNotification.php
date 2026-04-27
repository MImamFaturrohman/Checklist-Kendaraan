<?php

namespace App\Notifications;

use App\Models\Checklist;
use Illuminate\Notifications\Notification;

class ChecklistSubmittedNotification extends Notification
{
    public function __construct(
        public Checklist $checklist
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
        $this->checklist->loadMissing('user');
        $name = $this->checklist->user?->name
            ?? $this->checklist->user?->username
            ?? 'Pengguna';

        return [
            'title' => 'Ceklist kendaraan baru',
            'body' => "{$name} mengirim ceklist untuk {$this->checklist->nomor_kendaraan} ({$this->checklist->shift}).",
            'url' => route('admin.portal-pemeriksaan'),
            'checklist_id' => $this->checklist->id,
        ];
    }
}
