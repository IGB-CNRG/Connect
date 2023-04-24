<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Certificates;

use App\Entity\MemberCategory;
use App\Entity\Person;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class CertificateGuardSubscriber implements EventSubscriberInterface
{
    public function certificateGuard(GuardEvent $event): void
    {
        /** @var Person $subject */
        $subject = $event->getSubject();
        if(array_reduce(
            $subject->getMemberCategories(),
            fn(bool $carry, MemberCategory $category)=>$carry&&$category->isNeedsCertificates(),
            true
        )){
            $event->setBlocked(true);
        }
    }
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return ['workflow.membership.guard.activate_without_certificates'=>'certificateGuard'];
    }
}