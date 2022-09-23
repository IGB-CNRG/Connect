<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Security\Voter;

use App\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class StaffVoter extends Voter
{
    public const THEME_ADMIN = 'ROLE_THEME_ADMIN';
    public const LAB_MANAGER = 'ROLE_LAB_MANAGER';

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::THEME_ADMIN, self::LAB_MANAGER]);
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
            self::THEME_ADMIN => $user->getThemeAdminThemeAffiliations()->count() > 0,
            self::LAB_MANAGER => $user->getLabManagerThemeAffiliations()->count() > 0,
            default => false,
        };
    }
}
