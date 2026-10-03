<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function send(User|int $user, string $title, string $message, string $type = 'info'): Notification
    {
        return Notification::create([
            'user_id' => $user instanceof User ? $user->id : $user,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }
}
