<?php

namespace App\Entity;

use App\Enum\LanguageEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_card')]
class UserCard
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $user_id;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $card_id;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Assert\All([
        new Assert\Choice(choices: ['fr', 'reverse', 'jap'], message: 'Invalid LanguageEnum'),
    ])]
    private ?array $languages = null;

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getCardId(): int
    {
        return $this->card_id;
    }

    public function setCardId(int $card_id): self
    {
        $this->card_id = $card_id;
        return $this;
    }

    public function getLanguages(): array
    {
        if ($this->languages === null) {
            return [];
        }

        return array_map(fn(string $lang) => LanguageEnum::from($lang), $this->languages);
    }

    public function setLanguages(array $languages): self
    {
        $this->languages = empty($languages) ? null : array_map(fn(LanguageEnum $lang) => $lang->value, $languages);
        return $this;
    }
}
