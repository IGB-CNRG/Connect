<?php

namespace App\Security\Voter;

use App\Entity\Note;
use App\Entity\Person;
use App\Enum\NoteCategory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class NoteVoter extends Voter
{
    public const EDIT = 'NOTE_EDIT';
    public const VIEW = 'NOTE_VIEW';
    public const ADD = 'NOTE_ADD';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return (in_array($attribute, [self::EDIT, self::VIEW])
                && $subject instanceof Note)
               || ($attribute == self::ADD && $subject instanceof Person);
    }

    /**
     * @param string $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return bool
     */
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
            case self::ADD:
                return $this->security->isGranted('ROLE_CNRG')
                       || $this->security->isGranted('ROLE_OP_FAC')
                       || $this->security->isGranted('ROLE_THEME_ADMIN')
                       || $this->security->isGranted('ROLE_LAB_MANAGER'); // todo this might need some work
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                return $subject->getCreatedBy() === $user;
            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                switch ($subject->getType()) {
                    case NoteCategory::General:
                        return true;
                    case NoteCategory::IT:
                        return $this->security->isGranted('ROLE_CNRG');
                    case NoteCategory::Facilities:
                        return $this->security->isGranted('ROLE_OP_FAC');
                    case NoteCategory::Theme:
                        throw new \Exception('To be implemented');
                }
                break;
        }

        return false;
    }
}
