<?php

namespace App\Mail;

use App\Models\Cases;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CrimeNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $case, $type, $subjectText, $customMessage;

    public function __construct(User $user, ?Cases $case, string $type, ?string $subjectText = null, ?string $customMessage = null)
    {
        $this->user = $user;
        $this->case = $case;
        $this->type = $type;
        $this->subjectText = $subjectText;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        $subject = match ($this->type) {
            'new_case'    => '🚨 New Crime Reported in Your Area',
            'case_update' => '🔄 Update on a Case in Your Area',
            'alert'       => $this->subjectText ?? '📢 Safety Alert from District Core',
            default       => 'District Core Notification',
        };

        return $this->subject($subject)
                    ->view('emails.crime-notification');
    }
}
