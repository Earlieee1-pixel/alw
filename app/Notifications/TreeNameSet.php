<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TreeNameSet extends Notification
{
    use Queueable;

    /**
     * I-notify ang admin kung naa nag-set og name sa network tree.
     */
    public function __construct(
        public string $name,
        public int $position,
        public string $setBy
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
        return [
            'type'    => 'tree_name_set',
            'title'   => 'Network Tree Updated',
            'message' => "{$this->setBy} added \"{$this->name}\" to position {$this->position} on the network tree.",
            'url'     => '/network/tree',
            'avatar'  => 'T',
        ];
    }
}
