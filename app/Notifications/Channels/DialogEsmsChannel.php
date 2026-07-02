<?php

namespace App\Notifications\Channels;

use App\Notifications\Messages\DialogEsmsMessage;
use Erbitron\DialogESMS\DialogESMSClient;
use Illuminate\Notifications\Notification;
use RuntimeException;

class DialogEsmsChannel
{
    public function __construct(
        private readonly DialogESMSClient $client,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! is_string(config('dialog-esms.api_key')) || config('dialog-esms.api_key') === '') {
            return;
        }

        if (! method_exists($notification, 'toDialogEsms')) {
            return;
        }

        $recipient = $notifiable->routeNotificationFor('dialog_esms', $notification);

        if (! is_string($recipient) || trim($recipient) === '') {
            return;
        }

        $message = $notification->toDialogEsms($notifiable);

        if (! $message instanceof DialogEsmsMessage) {
            throw new RuntimeException('Dialog ESMS notifications must return a DialogEsmsMessage instance.');
        }

        $this->client->sendMessage(
            recipients: $recipient,
            message: $message->content,
            sourceAddress: $message->sourceAddress ?? (string) config('dialog-esms.source_address', 'LoomCraft'),
        );
    }
}
