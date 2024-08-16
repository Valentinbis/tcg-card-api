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

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::LIST]) ||
            (
                in_array($attribute, [self::EDIT, self::VIEW])
                && $subject instanceof Movement
            );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }
        
        switch ($attribute) {
            case self::EDIT:
                return $subject instanceof Movement && $subject->getUser() === $user;
                break;
            case self::CREATE:
                return true;
                break;
            case self::LIST:
                return true;
                break;
            case self::VIEW:
                return $subject instanceof Movement && $subject->getUser() === $user;
                break;
        }

        return false;
    }
}
