<?php
/*
 * Copyright (c) 2025 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Security\Voter;

use App\Entity\Person;
use App\Service\HistoricityManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PersonVoter extends Voter
{
    public const EDIT = 'PERSON_EDIT';
    public const VIEW = 'PERSON_VIEW';
    public const ADD = 'PERSON_ADD';
    public const EDIT_HISTORY = 'PERSON_EDIT_HISTORY';
    public const VIEW_DOCUMENTS = 'PERSON_VIEW_DOCUMENTS';
    public const VIEW_EXIT_REASON = 'PERSON_VIEW_EXIT_REASON';
    public const VIEW_LOG = 'PERSON_VIEW_LOG';

    public function __construct(
        private readonly Security $security,
        private readonly HistoricityManager $historicityManager
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return (in_array($attribute, [self::EDIT, self::VIEW])
                && $subject instanceof Person)
            || in_array($attribute, [self::ADD, self::EDIT_HISTORY, self::VIEW_DOCUMENTS, self::VIEW_EXIT_REASON, self::VIEW_LOG]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var Person $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Admins can do anything
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        return match ($attribute) {
            // anyone who can edit can also edit history, for now
            // todo who else can edit?
            self::EDIT, self::EDIT_HISTORY => $this->security->isGranted('ROLE_CERTIFICATE_MANAGER')
                || $this->security->isGranted('ROLE_APPROVER')
                || $this->security->isGranted('ROLE_HR'),
            self::VIEW => true,
            self::ADD, self::VIEW_DOCUMENTS, self::VIEW_EXIT_REASON => $this->security->isGranted('ROLE_APPROVER')
                || $this->security->isGranted('ROLE_HR'),
            self::VIEW_LOG => $this->security->isGranted('ROLE_CNRG'),

            default => false,
        };
    }
}
