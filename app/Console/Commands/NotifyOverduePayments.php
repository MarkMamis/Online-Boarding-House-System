<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\SystemNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyOverduePayments extends Command
{
    protected $signature = 'payments:notify-overdue';

    protected $description = 'Send daily in-app and email alerts for overdue tenant payments';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $notifiedCount = 0;

        $bookings = Booking::with(['room.property.landlord', 'student'])
            ->where('status', 'approved')
            ->where('check_out', '>', $today->toDateString())
            ->get();

        foreach ($bookings as $booking) {
            if (!$booking->isPaymentOverdue($today)) {
                continue;
            }

            if ($booking->last_overdue_notified_at && $booking->last_overdue_notified_at->isSameDay($today)) {
                continue;
            }

            $landlord = $booking->room?->property?->landlord;
            $student = $booking->student;
            if (!$landlord || !$student) {
                continue;
            }

            $dueDateLabel = optional($booking->resolvePaymentDueDate())->format('M d, Y') ?? 'previous due date';

            try {
                $landlord->notify(new SystemNotification(
                    'Overdue payment detected',
                    sprintf(
                        '%s is overdue for Room %s at %s (due %s).',
                        $student->full_name,
                        $booking->room->room_number,
                        $booking->room->property->name,
                        $dueDateLabel
                    ),
                    route('landlord.payments.index', ['status' => 'overdue']),
                    [
                        'booking_id' => $booking->id,
                        'student_id' => $student->id,
                        'due_date' => $dueDateLabel,
                    ]
                ));
            } catch (\Throwable $e) {
                // Ignore notification storage errors.
            }

            try {
                $student->notify(new SystemNotification(
                    'Payment overdue',
                    sprintf(
                        'Your payment for Room %s at %s is overdue (due %s). Please settle your payment and contact your landlord.',
                        $booking->room->room_number,
                        $booking->room->property->name,
                        $dueDateLabel
                    ),
                    route('student.bookings.index'),
                    [
                        'booking_id' => $booking->id,
                        'due_date' => $dueDateLabel,
                    ]
                ));
            } catch (\Throwable $e) {
                // Ignore notification storage errors.
            }

            try {
                Mail::raw(
                    sprintf(
                        "Overdue payment alert:\nTenant: %s\nProperty: %s\nRoom: %s\nDue date: %s\n\nOpen landlord payments to review and send reminder.",
                        $student->full_name,
                        $booking->room->property->name,
                        $booking->room->room_number,
                        $dueDateLabel
                    ),
                    function ($message) use ($landlord) {
                        $message->to($landlord->email)->subject('Overdue Payment Alert');
                    }
                );
            } catch (\Throwable $e) {
                // Ignore email transport errors.
            }

            $booking->last_overdue_notified_at = now();
            $booking->save();
            $notifiedCount++;
        }

        $this->info("Overdue notifications sent: {$notifiedCount}");

        return Command::SUCCESS;
    }
}
