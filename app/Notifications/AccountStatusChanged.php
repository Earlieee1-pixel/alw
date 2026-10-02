<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountStatusChanged extends Notification
{
    use Queueable;

    /**
     * I-notify ang member kung nausab ang status sa ilang account.
     */
    public function __construct(public string $status) {}

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
        $isActive = $this->status === 'active';

        return [
            'type'    => 'account_status',
            'title'   => $isActive ? 'Account Activated' : 'Account Suspended',
            'message' => $isActive
                ? 'Your ALW account has been activated. Welcome back!'
                : 'Your ALW account has been suspended. Contact your upline for assistance.',
            'url'     => '/dashboard',
            'avatar'  => $isActive ? '✓' : '!',
        ];
    }
}
