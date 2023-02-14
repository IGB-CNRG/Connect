<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\SubmissionDate;

use App\Entity\Person;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Sets the membershipUpdatedAt field whenever the membership workflow completes a transition
 */
class SubmissionDateSubscriber implements EventSubscriberInterface
{
    public function setSubmissionDate(Event $event)
    {
        /** @var Person $person */
        $person = $event->getSubject();
        $person->setMembershipUpdatedAt(new \DateTimeImmutable());
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.completed' => 'setSubmissionDate',
        ];
    }
}