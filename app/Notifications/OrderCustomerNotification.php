<?php

namespace App\Notifications;

use App\Notifications\Channels\DialogEsmsChannel;
use App\Notifications\Messages\DialogEsmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCustomerNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $orderNumber,
        public readonly string $event,
        public readonly string $subject,
        public readonly string $message,
        public readonly string $actionUrl,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->routeNotificationFor('mail', $this) !== null) {
            $channels[] = 'mail';
        }

        if ($notifiable->routeNotificationFor('dialog_esms', $this) !== null) {
            $channels[] = DialogEsmsChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello from LoomCraft')
            ->line($this->message)
            ->line("Order number: {$this->orderNumber}")
            ->action('View order', $this->actionUrl)
            ->line('Thank you for choosing LoomCraft.');
    }

    public function toDialogEsms(object $notifiable): DialogEsmsMessage
    {
        return new DialogEsmsMessage(
            content: "{$this->subject}: {$this->message} Order {$this->orderNumber}. {$this->actionUrl}",
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_number' => $this->orderNumber,
            'event' => $this->event,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
        ];
    }
}
