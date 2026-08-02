<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPublicEnquiryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $type,
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly string $reference,
        public readonly string $adminUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New {$this->type} from {$this->customerName}")
            ->greeting('A new website enquiry has arrived')
            ->line("Customer: {$this->customerName}")
            ->line("Email: {$this->customerEmail}")
            ->line("Reference: {$this->reference}")
            ->action('View in admin', $this->adminUrl)
            ->line('Please follow up through the Sri Soul Ventures admin panel.');
    }
}
