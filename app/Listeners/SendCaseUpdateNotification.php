<?php

namespace App\Listeners;

use App\Events\CaseUpdated;
use App\Services\EmailNotificationService;

class SendCaseUpdateNotification
{
    public function handle(CaseUpdated $event): void
    {
        (new EmailNotificationService)->notifyResidentsOfCaseUpdate($event->case);
    }
}
