<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewDownline extends Notification
{
    use Queueable;

    /**
     * I-notify ang upline kung nag-register ang bag-ong downline.
     */
    public function __construct(public User $newMember) {}

    /**
     * I-store sa database lang — no email for now.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Notification data nga i-store sa database.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'new_downline',
            'title'   => 'New member joined your network',
            'message' => "{$this->newMember->name} just registered using your invite link.",
            'url'     => '/network/tree',
            'avatar'  => $this->newMember->name[0],
        ];
    }
}
