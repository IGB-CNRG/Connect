<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Workflow\Digest;

use App\Entity\DigestBuffer;
use App\Entity\Person;
use App\Entity\Theme;
use App\Entity\ThemeAffiliation;
use App\Repository\DigestBufferRepository;
use App\Service\HistoricityManagerAware;
use App\Workflow\Membership;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class DigestSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;
    use HistoricityManagerAware;

    public function bufferDigestMessage(Event $event): void
    {
        $transition = $event->getTransition()->getName();
        $bufferName = match ($transition) {
            Membership::TRANS_ACTIVATE_WITHOUT_CERTIFICATES,
            Membership::TRANS_APPROVE_ENTRY_FORM,
            Membership::TRANS_FORCE_ENTRY_FORM => DigestBuffer::ENTRY_BUFFER,
            Membership::TRANS_FORCE_EXIT_FORM, Membership::TRANS_DEACTIVATE => DigestBuffer::EXIT_BUFFER,
            default => null,
        };
        if ($bufferName) {
            /** @var Person $subject */
            $subject = $event->getSubject();
            $date = match ($bufferName) {
                DigestBuffer::ENTRY_BUFFER => $this->historicityManager()->getEarliest(
                    $subject->getThemeAffiliations()
                ),
                DigestBuffer::EXIT_BUFFER => $this->historicityManager()->getLatest($subject->getThemeAffiliations()),
            };
            $themes = array_unique(
                array_map(fn(ThemeAffiliation $themeAffiliation) => $themeAffiliation->getTheme(),
                    $subject->getThemeAffiliations()->filter(function (ThemeAffiliation $themeAffiliation) use ($date
                    ) {
                        return $themeAffiliation->wasCurrentAtDate($date);
                    })->toArray())
            );
            $notes = join(', ', array_map(fn(Theme $theme) => $theme->getShortName(), $themes));

            $buffer = (new DigestBuffer())
                ->setPerson($subject)
                ->setBufferName($bufferName)
                ->setDate($date)
                ->setNotes($notes);
            $this->digestBufferRepository()->save($buffer);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.completed' => 'bufferDigestMessage',
        ];
    }

    //MARK: - Service Subscribers
    #[SubscribedService]
    private function digestBufferRepository(): DigestBufferRepository
    {
        return $this->container->get(__CLASS__.'::'.__FUNCTION__);
    }
}