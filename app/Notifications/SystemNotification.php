<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public array $meta = [],
        public bool $sendEmail = false,
        public ?string $actionText = null
    ) {
    }

    /**
     * Determine notification delivery channels.
     * Always delivers to in-app database notifications; includes mail channel when warranted.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->shouldSendEmail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Check whether transactional email delivery should occur for this notification and recipient.
     */
    public function shouldSendEmail(object $notifiable): bool
    {
        if (empty($notifiable->email)) {
            return false;
        }

        return $this->sendEmail || !empty($this->meta['send_email']);
    }

    /**
     * Internal database notification representation.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'meta' => $this->meta,
        ];
    }

    /**
     * Transactional email representation rendered via OBHS branded templates.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $recipientName = $notifiable->full_name ?? $notifiable->name ?? 'User';
        $actionUrl = $this->url ? url($this->url) : null;
        $actionText = $this->actionText ?? ($this->meta['action_text'] ?? 'View Details in OBHS');

        return (new MailMessage)
            ->subject($this->title . ' - OBHS')
            ->view('emails.transactional', [
                'recipientName' => $recipientName,
                'title' => $this->title,
                'contentMessage' => $this->message,
                'actionUrl' => $actionUrl,
                'actionText' => $actionText,
                'meta' => $this->meta,
            ])
            ->text('emails.transactional-text', [
                'recipientName' => $recipientName,
                'title' => $this->title,
                'contentMessage' => $this->message,
                'actionUrl' => $actionUrl,
                'actionText' => $actionText,
                'meta' => $this->meta,
            ]);
    }
}
