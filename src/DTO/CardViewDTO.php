<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class CardViewDTO
{
    #[Groups(['card:read'])]
    public int $id;

    #[Groups(['card:read'])]
    public string $name;

    #[Groups(['card:read'])]
    public string $nameFr;

    #[Groups(['card:read'])]
    public int $number;

    #[Groups(['card:read'])]
    public string $rarity;

    #[Groups(['card:read'])]
    public array $nationalPokedexNumbers;

    #[Groups(['card:read'])]
    public array $images;

    #[Groups(['card:read'])]
    public array $owned_languages;

    public function __construct(
        int $id,
        string $name,
        string $nameFr,
        int $number,
        string $rarity,
        array $nationalPokedexNumbers,
        array $images,
        array $ownedLanguages = []
    )
    {
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
