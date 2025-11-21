<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CardVariantEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'card_variant')]
class CardVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Card::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Card $card = null;

    #[ORM\Column(type: 'string', length: 10, enumType: CardVariantEnum::class)]
    private CardVariantEnum $type;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $price = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_average = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_trend = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_min = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_max = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_reverse = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_holo = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_market = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_low = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_mid = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_high = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_direct = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_suggested = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_germanProLow = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_low_ex_plus = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_avg1 = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_avg7 = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_avg30 = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_reverse_low = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_reverse_trend = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_reverse_avg1 = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_reverse_avg7 = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $cardmarket_reverse_avg30 = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_normal_low = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_normal_mid = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_normal_high = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_normal_market = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_normal_direct = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_reverse_low = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_reverse_mid = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_reverse_high = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_reverse_market = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_reverse_direct = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_holo_low = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_holo_mid = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_holo_high = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_holo_market = null;
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $tcgplayer_holo_direct = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): self
    {
        $this->card = $card;

        return $this;
    }

    public function getType(): CardVariantEnum
    {
        return $this->type;
    }

    public function setType(CardVariantEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCardmarket(): ?float
    {
        return $this->cardmarket;
    }

    public function setCardmarket(?float $cardmarket): self
    {
        $this->cardmarket = $cardmarket;

        return $this;
    }

    public function getCardmarketAverage(): ?float
    {
        return $this->cardmarket_average;
    }

    public function setCardmarketAverage(?float $v): self
    {
        $this->cardmarket_average = $v;

        return $this;
    }

    public function getCardmarketTrend(): ?float
    {
        return $this->cardmarket_trend;
    }

    public function setCardmarketTrend(?float $v): self
    {
        $this->cardmarket_trend = $v;

        return $this;
    }

    public function getCardmarketMin(): ?float
    {
        return $this->cardmarket_min;
    }

    public function setCardmarketMin(?float $v): self
    {
        $this->cardmarket_min = $v;

        return $this;
    }

    public function getCardmarketMax(): ?float
    {
        return $this->cardmarket_max;
    }

    public function setCardmarketMax(?float $v): self
    {
        $this->cardmarket_max = $v;

        return $this;
    }

    public function getCardmarketReverse(): ?float
    {
        return $this->cardmarket_reverse;
    }

    public function setCardmarketReverse(?float $v): self
    {
        $this->cardmarket_reverse = $v;

        return $this;
    }

    public function getCardmarketHolo(): ?float
    {
        return $this->cardmarket_holo;
    }

    public function setCardmarketHolo(?float $v): self
    {
        $this->cardmarket_holo = $v;

        return $this;
    }

    public function getTcgplayer(): ?float
    {
        return $this->tcgplayer;
    }

    public function setTcgplayer(?float $tcgplayer): self
    {
        $this->tcgplayer = $tcgplayer;

        return $this;
    }

    public function getTcgplayerMarket(): ?float
    {
        return $this->tcgplayer_market;
    }

    public function setTcgplayerMarket(?float $v): self
    {
        $this->tcgplayer_market = $v;

        return $this;
    }

    public function getTcgplayerLow(): ?float
    {
        return $this->tcgplayer_low;
    }

    public function setTcgplayerLow(?float $v): self
    {
        $this->tcgplayer_low = $v;

        return $this;
    }

    public function getTcgplayerMid(): ?float
    {
        return $this->tcgplayer_mid;
    }

    public function setTcgplayerMid(?float $v): self
    {
        $this->tcgplayer_mid = $v;

        return $this;
    }

    public function getTcgplayerHigh(): ?float
    {
        return $this->tcgplayer_high;
    }

    public function setTcgplayerHigh(?float $v): self
    {
        $this->tcgplayer_high = $v;

        return $this;
    }

    public function getTcgplayerDirect(): ?float
    {
        return $this->tcgplayer_direct;
    }

    public function setTcgplayerDirect(?float $v): self
    {
        $this->tcgplayer_direct = $v;

        return $this;
    }

    public function getCardmarketSuggested(): ?float
    {
        return $this->cardmarket_suggested;
    }

    public function setCardmarketSuggested(?float $v): self
    {
        $this->cardmarket_suggested = $v;

        return $this;
    }

    public function getCardmarketGermanProLow(): ?float
    {
        return $this->cardmarket_germanProLow;
    }

    public function setCardmarketGermanProLow(?float $v): self
    {
        $this->cardmarket_germanProLow = $v;

        return $this;
    }

    public function getCardmarketLowExPlus(): ?float
    {
        return $this->cardmarket_low_ex_plus;
    }

    public function setCardmarketLowExPlus(?float $v): self
    {
        $this->cardmarket_low_ex_plus = $v;

        return $this;
    }

    public function getCardmarketAvg1(): ?float
    {
        return $this->cardmarket_avg1;
    }

    public function setCardmarketAvg1(?float $v): self
    {
        $this->cardmarket_avg1 = $v;

        return $this;
    }

    public function getCardmarketAvg7(): ?float
    {
        return $this->cardmarket_avg7;
    }

    public function setCardmarketAvg7(?float $v): self
    {
        $this->cardmarket_avg7 = $v;

        return $this;
    }

    public function getCardmarketAvg30(): ?float
    {
        return $this->cardmarket_avg30;
    }

    public function setCardmarketAvg30(?float $v): self
    {
        $this->cardmarket_avg30 = $v;

        return $this;
    }

    public function getCardmarketReverseLow(): ?float
    {
        return $this->cardmarket_reverse_low;
    }

    public function setCardmarketReverseLow(?float $v): self
    {
        $this->cardmarket_reverse_low = $v;

        return $this;
    }

    public function getCardmarketReverseTrend(): ?float
    {
        return $this->cardmarket_reverse_trend;
    }

    public function setCardmarketReverseTrend(?float $v): self
    {
        $this->cardmarket_reverse_trend = $v;

        return $this;
    }

    public function getCardmarketReverseAvg1(): ?float
    {
        return $this->cardmarket_reverse_avg1;
    }

    public function setCardmarketReverseAvg1(?float $v): self
    {
        $this->cardmarket_reverse_avg1 = $v;

        return $this;
    }

    public function getCardmarketReverseAvg7(): ?float
    {
        return $this->cardmarket_reverse_avg7;
    }

    public function setCardmarketReverseAvg7(?float $v): self
    {
        $this->cardmarket_reverse_avg7 = $v;

        return $this;
    }

    public function getCardmarketReverseAvg30(): ?float
    {
        return $this->cardmarket_reverse_avg30;
    }

    public function setCardmarketReverseAvg30(?float $v): self
    {
        $this->cardmarket_reverse_avg30 = $v;

        return $this;
    }

    public function getTcgplayerNormalLow(): ?float
    {
        return $this->tcgplayer_normal_low;
    }

    public function setTcgplayerNormalLow(?float $v): self
    {
        $this->tcgplayer_normal_low = $v;

        return $this;
    }

    public function getTcgplayerNormalMid(): ?float
    {
        return $this->tcgplayer_normal_mid;
    }

    public function setTcgplayerNormalMid(?float $v): self
    {
        $this->tcgplayer_normal_mid = $v;

        return $this;
    }

    public function getTcgplayerNormalHigh(): ?float
    {
        return $this->tcgplayer_normal_high;
    }

    public function setTcgplayerNormalHigh(?float $v): self
    {
        $this->tcgplayer_normal_high = $v;

        return $this;
    }

    public function getTcgplayerNormalMarket(): ?float
    {
        return $this->tcgplayer_normal_market;
    }

    public function setTcgplayerNormalMarket(?float $v): self
    {
        $this->tcgplayer_normal_market = $v;

        return $this;
    }

    public function getTcgplayerNormalDirect(): ?float
    {
        return $this->tcgplayer_normal_direct;
    }

    public function setTcgplayerNormalDirect(?float $v): self
    {
        $this->tcgplayer_normal_direct = $v;

        return $this;
    }

    public function getTcgplayerReverseLow(): ?float
    {
        return $this->tcgplayer_reverse_low;
    }

    public function setTcgplayerReverseLow(?float $v): self
    {
        $this->tcgplayer_reverse_low = $v;

        return $this;
    }

    public function getTcgplayerReverseMid(): ?float
    {
        return $this->tcgplayer_reverse_mid;
    }

    public function setTcgplayerReverseMid(?float $v): self
    {
        $this->tcgplayer_reverse_mid = $v;

        return $this;
    }

    public function getTcgplayerReverseHigh(): ?float
    {
        return $this->tcgplayer_reverse_high;
    }

    public function setTcgplayerReverseHigh(?float $v): self
    {
        $this->tcgplayer_reverse_high = $v;

        return $this;
    }

    public function getTcgplayerReverseMarket(): ?float
    {
        return $this->tcgplayer_reverse_market;
    }

    public function setTcgplayerReverseMarket(?float $v): self
    {
        $this->tcgplayer_reverse_market = $v;

        return $this;
    }

    public function getTcgplayerReverseDirect(): ?float
    {
        return $this->tcgplayer_reverse_direct;
    }

    public function setTcgplayerReverseDirect(?float $v): self
    {
        $this->tcgplayer_reverse_direct = $v;

        return $this;
    }

    public function getTcgplayerHoloLow(): ?float
    {
        return $this->tcgplayer_holo_low;
    }

    public function setTcgplayerHoloLow(?float $v): self
    {
        $this->tcgplayer_holo_low = $v;

        return $this;
    }

    public function getTcgplayerHoloMid(): ?float
    {
        return $this->tcgplayer_holo_mid;
    }

    public function setTcgplayerHoloMid(?float $v): self
    {
        $this->tcgplayer_holo_mid = $v;

        return $this;
    }

    public function getTcgplayerHoloHigh(): ?float
    {
        return $this->tcgplayer_holo_high;
    }

    public function setTcgplayerHoloHigh(?float $v): self
    {
        $this->tcgplayer_holo_high = $v;

        return $this;
    }

    public function getTcgplayerHoloMarket(): ?float
    {
        return $this->tcgplayer_holo_market;
    }

    public function setTcgplayerHoloMarket(?float $v): self
    {
        $this->tcgplayer_holo_market = $v;

        return $this;
    }

    public function getTcgplayerHoloDirect(): ?float
    {
        return $this->tcgplayer_holo_direct;
    }

    public function setTcgplayerHoloDirect(?float $v): self
    {
        $this->tcgplayer_holo_direct = $v;

        return $this;
    }
}
