<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\LanguageEnum;
use App\Repository\UserCardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserCardRepository::class)]
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
    /** @var array<string>|null */
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

    /**
     * @return array<LanguageEnum>
     */
    public function getLanguages(): array
    {
        if (null === $this->languages) {
            return [];
        }

        return array_map(
            function (mixed $lang): LanguageEnum {
                assert(is_string($lang));
                return LanguageEnum::from($lang);
            },
            $this->languages
        );
    }

    /**
     * @param array<LanguageEnum> $languages
     */
    public function setLanguages(array $languages): self
    {
        $this->languages = empty($languages) ? null : array_map(fn (LanguageEnum $lang): string => $lang->value, $languages);

        return $this;
    }
}
