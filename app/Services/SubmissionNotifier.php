<?php

namespace App\Services;

use App\Models\Submission;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SubmissionNotifier
{
    /**
     * Send the submission details to the success recipients.
     *
     * @throws Throwable
     */
    public static function notifySuccess(Submission $submission): void
    {
        $recipients = config('submissions.email.success_recipients', []);
        if (empty($recipients)) {
            return;
        }

        $submission->loadMissing('dealer');

        $message = self::formatSubmissionMessage($submission);

        Mail::raw($message, function ($mail) use ($recipients) {
            $mail->to($recipients)->subject('New KYCN Registration');
        });
    }

    /**
     * Alert failure recipients when a submission cannot be processed or emailed.
     */
    public static function notifyFailure(array $context, ?Throwable $exception = null): void
    {
        $recipients = config('submissions.email.failure_recipients', []);
        if (empty($recipients)) {
            return;
        }

        $body = "A Know Your Car Night submission failed to send.\n\n";
        $body .= "Context:\n".json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($exception) {
            $body .= "\n\nError: ".$exception->getMessage();
        }

        Mail::raw($body, function ($mail) use ($recipients) {
            $mail->to($recipients)->subject('KYCN Submission Failure');
        });
    }

    /**
     * Build the email body that mirrors the legacy plain-text format.
     */
    protected static function formatSubmissionMessage(Submission $submission): string
    {
        $dealerName = $submission->dealer?->name ?? 'Unknown Dealer';

        $lines = [
            'New KYCN Registration',
            '',
            "Dealer: {$dealerName}",
            "Name:   {$submission->full_name}",
            "Email:  {$submission->email}",
            "Phone:  {$submission->phone}",
            "Guests: {$submission->guest_count}",
        ];

        if ($submission->know_your_car_date) {
            $lines[] = 'KYCN Date: '.$submission->know_your_car_date->format('M jS, Y');
        }

        if ($submission->vehicle_purchased) {
            $lines[] = 'Vehicle Purchased: '.$submission->vehicle_purchased->format('M jS, Y');
        }

        return implode("\n", $lines);
    }
}
