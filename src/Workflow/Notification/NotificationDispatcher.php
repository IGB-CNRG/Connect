<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Notification;

use App\Entity\Person;
use App\Entity\WorkflowNotification;

class NotificationDispatcher
{
    public function sendNotification(WorkflowNotification $notification, Person $person):void
    {
        // todo stub
    }
}