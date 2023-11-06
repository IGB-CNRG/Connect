<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Security\Voter;

use App\Entity\Person;
use App\Entity\ThemeAffiliation;
use App\Entity\ThemeRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class StaffVoter extends Voter
{
    public const APPROVER = 'ROLE_APPROVER';

    public function __construct(private readonly Security $security) {}

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::APPROVER]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var Person $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        return match ($attribute) {
            // todo this may be naive, but it works for now
            self::APPROVER => $this->security->isGranted('ROLE_CERTIFICATE_MANAGER')
                || $this->isApprover($user),
            default => false,
        };
    }

    private function isApprover(Person $person): bool
    {
        // Return true if the person has at least one theme affiliation with at least one approver role
        return $person->getThemeAffiliations()->filter(
                fn(ThemeAffiliation $themeAffiliation) => $themeAffiliation->getRoles()->filter(
                    fn(ThemeRole $role) => $role->isIsApprover())->count() > 0
            )->count() > 0;
    }
}
