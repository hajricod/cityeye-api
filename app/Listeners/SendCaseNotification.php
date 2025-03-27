<?php

namespace App\Listeners;

use App\Events\CaseCreated;
use App\Services\EmailNotificationService;

class SendCaseNotification
{
    public function handle(CaseCreated $event): void
    {
        (new EmailNotificationService)->notifyResidentsOfNewCase($event->case);
    }
}
