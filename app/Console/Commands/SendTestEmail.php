<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obhs:mail-test
                            {email : Destination email address for the test}
                            {--mailer= : Optional mailer override (defaults to configured MAIL_MAILER)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely dispatch a controlled transactional test email to verify Resend or local mail transport configuration.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));

        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email:rfc,dns',
        ]);

        if ($validator->fails()) {
            // Also accept standard rfc email for local/offline testing
            $rfcValidator = Validator::make(['email' => $email], [
                'email' => 'required|email:rfc',
            ]);

            if ($rfcValidator->fails()) {
                $this->error('Invalid recipient email address provided: "' . $email . '".');
                return self::FAILURE;
            }
        }

        $mailer = $this->option('mailer') ?: config('mail.default', 'log');

        $this->info('Preparing to send test email:');
        $this->line('  Mailer:      ' . $mailer);
        $this->line('  Recipient:   ' . $email);
        $this->line('  Sender:      ' . config('mail.from.address') . ' (' . config('mail.from.name') . ')');

        if ($mailer === 'resend' && empty(config('services.resend.key'))) {
            $this->error('Error: RESEND_API_KEY is not configured in services.resend.key or .env.');
            return self::FAILURE;
        }

        try {
            Mail::mailer($mailer)->send('emails.test', [
                'mailer' => $mailer,
                'sentAt' => now()->toFormattedDateString() . ' ' . now()->toTimeString(),
                'environment' => app()->environment(),
                'recipientEmail' => $email,
                'appUrl' => config('app.url'),
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('OBHS Email Integration Test - Successful');
            });

            $this->info('✓ Test email dispatched successfully via [' . $mailer . '].');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to dispatch test email.');
            $this->line('Message: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
