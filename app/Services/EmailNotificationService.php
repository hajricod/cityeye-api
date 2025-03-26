<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\CrimeNotification;
use App\Models\User;
use App\Models\Cases;

class EmailNotificationService
{
    /**
     * Notify users in the area about a new case.
     */
    public function notifyResidentsOfNewCase(Cases $case)
    {
        $users = User::all();

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new CrimeNotification($user, $case, 'new_case'));
        }
    }

    /**
     * Notify users of an update on a specific case.
     */
    public function notifyResidentsOfCaseUpdate(Cases $case)
    {
        $users = User::all();

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new CrimeNotification($user, $case, 'case_update'));
        }
    }

    /**
     * Notify all users of a safety alert.
     */
    public function sendSafetyAlert(string $subject, string $message)
    {
        $users = User::all();

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new CrimeNotification($user, null, 'alert', $subject, $message));
        }
    }
}
