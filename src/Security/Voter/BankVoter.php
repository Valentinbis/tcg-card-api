<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BankVoter extends Voter
{
    public const EDIT = 'BANK_EDIT';
    public const VIEW = 'BANK_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {

        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Bank;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
            case self::VIEW:
        }

        return false;
    }
}
