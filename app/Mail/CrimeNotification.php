<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Cases;
use Illuminate\Mail\Mailable;

class CrimeNotification extends Mailable
{
    public function __construct(
        public User $user,
        public ?Cases $case,
        public string $type,
        public ?string $subjectText = null,
        public ?string $customMessage = null
    ) {}

    public function build()
    {
        $subject = match ($this->type) {
            'new_case'    => '🚨 New Crime Reported in Your Area',
            'case_update' => '🔄 Update on a Case in Your Area',
            'alert'       => $this->subjectText ?? '📢 Safety Alert',
            default       => 'District Core Notification',
        };

        return $this->subject($subject)
                    ->view('emails.crime-notification');
    }
}

