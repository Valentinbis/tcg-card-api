<?php

namespace App\Security\Voter;

use App\Entity\Category;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CategoryVoter extends Voter
{
    public const EDIT = 'CATEGORY_EDIT';
    public const VIEW = 'CATEGORY_VIEW';
    public const LIST = 'CATEGORY_LIST';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // return in_array($attribute, [self::EDIT, self::VIEW])
        //     && $subject instanceof \App\Entity\Category;
        return in_array($attribute, [self::LIST]) ||
            (
                in_array($attribute, [self::EDIT, self::VIEW])
                && $subject instanceof Category
            );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
            case self::VIEW:
            case self::LIST:
                return true;
                break;
        }

        return false;
    }
}
