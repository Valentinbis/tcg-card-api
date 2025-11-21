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

    #[Groups(['card:read'])]
    public array $images;

    #[Groups(['card:read'])]
    public bool $owned;

    /**
     * @var array<string, array<string, float|null>>
     */
    #[Groups(['card:read'])]
    public array $variants = [];

    /**
     * @param array<int>            $nationalPokedexNumbers
     * @param array<string, string> $images
     */
    public function __construct(
        string $id,
        string $name,
        string $nameFr,
        string $number,
        string $rarity,
        array $nationalPokedexNumbers,
        array $images,
        bool $owned = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->nameFr = $nameFr;
        $this->number = $number;
        $this->rarity = $rarity;
        $this->nationalPokedexNumbers = $nationalPokedexNumbers;
        $this->images = $images;
        $this->owned = $owned;
    }
}
