<?php

namespace App\Notifications;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewVideo extends Notification
{
    use Queueable;

    /**
     * I-notify ang tanan nga members kung naa bag-ong training video.
     */
    public function __construct(public Video $video) {}

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
            'type'    => 'new_video',
            'title'   => 'New Training Video',
            'message' => "\"{$this->video->title}\" has been added to the training library.",
            'url'     => "/videos/{$this->video->id}",
            'avatar'  => 'V',
        ];
    }
}
