<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class CardViewDTO
{
    #[Groups(['card:read'])]
    public string $id;

    #[Groups(['card:read'])]
    public string $name;

    #[Groups(['card:read'])]
    public string $nameFr;

    #[Groups(['card:read'])]
    public string $number;

    #[Groups(['card:read'])]
    public string $rarity;

    /** @var array<int> */
    #[Groups(['card:read'])]
    public array $nationalPokedexNumbers;

    /** @var array<string, string> */
    #[Groups(['card:read'])]
    public array $images;

    /** @var array<string> */
    #[Groups(['card:read'])]
    public array $owned_languages;

    /**
     * @param array<int> $nationalPokedexNumbers
     * @param array<string, string> $images
     * @param array<string> $ownedLanguages
     */
    public function __construct(
        string $id,
        string $name,
        string $nameFr,
        string $number,
        string $rarity,
        array $nationalPokedexNumbers,
        array $images,
        array $ownedLanguages = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->nameFr = $nameFr;
        $this->number = $number;
        $this->rarity = $rarity;
        $this->nationalPokedexNumbers = $nationalPokedexNumbers;
        $this->images = $images;
        $this->owned_languages = $ownedLanguages;
    }
}
