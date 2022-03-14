<?php

namespace App\Security\Voter;

use App\Entity\Person;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PersonVoter extends Voter
{
    public const EDIT = 'PERSON_EDIT';
    public const VIEW = 'PERSON_VIEW';
    public const ADD = 'PERSON_ADD';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return (in_array($attribute, [self::EDIT, self::VIEW])
                && $subject instanceof Person)
               || $attribute === self::ADD;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $subject === $user
                       || $this->security->isGranted('ROLE_HUMAN_RESOURCES')
                    // todo who else can edit?
                    // todo once theme admins are set up, fix this
                    // todo this may need to be more granular, e.g., can someone edit their own theme affiliations?
                    ;
            case self::VIEW:
                return true;
            case self::ADD:
                return $this->security->isGranted('ROLE_HUMAN_RESOURCES'); // todo who else can add?
        }

        return false;
    }
}
