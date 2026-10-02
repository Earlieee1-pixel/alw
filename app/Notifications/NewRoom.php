<?php

namespace App\Notifications;

use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRoom extends Notification
{
    use Queueable;

    /**
     * I-notify ang tanan nga members kung naa bag-ong video call room.
     */
    public function __construct(public Room $room) {}

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
            'type'    => 'new_room',
            'title'   => 'New Video Call Room',
            'message' => "A new room \"{$this->room->title}\" is now open. Room code: {$this->room->room_code}",
            'url'     => "/calls/{$this->room->id}",
            'avatar'  => 'C',
        ];
    }
}
