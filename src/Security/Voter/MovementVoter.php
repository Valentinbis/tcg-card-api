<?php

namespace App\Security\Voter;

use App\Entity\Movement;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MovementVoter extends Voter
{
    public const CREATE = 'MOVEMENT_CREATE';
    public const EDIT = 'MOVEMENT_EDIT';
    public const VIEW = 'MOVEMENT_VIEW';
    public const LIST = 'MOVEMENT_LIST';
    public const LIST_ALL = 'MOVEMENT_LIST_ALL';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::LIST, self::LIST_ALL]) || (in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Movement);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $subject->getUser() === $user;
                break;

            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;
            case self::CREATE:
                return true;
                break;
            case self::LIST:
                return true;
                break;
            case self::LIST_ALL:
                // return in_array('ROLE_ADMIN', $user->getRoles());
                return false;
                break;
        }

        return false;
    }
}
