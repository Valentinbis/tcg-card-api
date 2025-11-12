<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserSettingsRepository::class)]
#[ORM\Table(name: 'user_settings')]
class UserSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'L\'utilisateur est requis')]
    private User $user;

    // Paramètres d'affichage
    #[ORM\Column(type: 'integer', options: ['default' => 20])]
    #[Groups(['settings.show'])]
    #[Assert\NotNull(message: 'Le nombre de cartes par page est requis')]
    #[Assert\Range(
        min: 10,
        max: 100,
        notInRangeMessage: 'Le nombre de cartes par page doit être entre {{ min }} et {{ max }}'
    )]
    private int $cardsPerPage = 20;

    #[ORM\Column(type: 'string', length: 10, options: ['default' => 'grid'])]
    #[Groups(['settings.show'])]
    #[Assert\NotBlank(message: 'La vue par défaut est requise')]
    #[Assert\Choice(
        choices: ['grid', 'list'],
        message: 'La vue doit être "grid" ou "list"'
    )]
    private string $defaultView = 'grid';

    #[ORM\Column(type: 'string', length: 5, options: ['default' => 'fr'])]
    #[Groups(['settings.show'])]
    #[Assert\NotBlank(message: 'La langue par défaut est requise')]
    #[Assert\Choice(
        choices: ['fr', 'en', 'es', 'de', 'it', 'ja'],
        message: 'La langue doit être l\'une des valeurs supportées'
    )]
    private string $defaultLanguage = 'fr';

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['settings.show'])]
    #[Assert\NotNull(message: 'Le paramètre d\'affichage des numéros est requis')]
    private bool $showCardNumbers = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['settings.show'])]
    #[Assert\NotNull(message: 'Le paramètre d\'affichage des prix est requis')]
    private bool $showPrices = true;

    // Notifications
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['settings.show'])]
    #[Assert\NotNull(message: 'Le paramètre de notifications email est requis')]
    private bool $emailNotifications = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['settings.show'])]
    #[Assert\NotNull(message: 'Le paramètre d\'alertes nouvelles cartes est requis')]
    private bool $newCardAlerts = true;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['settings.show'])]
    #[Assert\NotNull(message: 'Le paramètre d\'alertes baisse de prix est requis')]
    private bool $priceDropAlerts = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['settings.show'])]
    #[Assert\NotNull(message: 'Le paramètre de rapport hebdomadaire est requis')]
    private bool $weeklyReport = false;

    // Confidentialité
    #[ORM\Column(type: 'string', length: 10, options: ['default' => 'public'])]
    #[Groups(['settings.show'])]
    #[Assert\NotBlank(message: 'La visibilité du profil est requise')]
    #[Assert\Choice(
        choices: ['public', 'private', 'friends'],
        message: 'La visibilité doit être "public", "private" ou "friends"'
    )]
    private string $profileVisibility = 'public';

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['settings.show'])]
    #[Assert\NotNull(message: 'Le paramètre d\'affichage de la collection est requis')]
    private bool $showCollection = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['settings.show'])]
    #[Assert\NotNull(message: 'Le paramètre d\'affichage de la wishlist est requis')]
    private bool $showWishlist = true;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCardsPerPage(): int
    {
        return $this->cardsPerPage;
    }

    public function setCardsPerPage(int $cardsPerPage): self
    {
        $this->cardsPerPage = $cardsPerPage;

        return $this;
    }

    public function getDefaultView(): string
    {
        return $this->defaultView;
    }

    public function setDefaultView(string $defaultView): self
    {
        $this->defaultView = $defaultView;

        return $this;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    public function setDefaultLanguage(string $defaultLanguage): self
    {
        $this->defaultLanguage = $defaultLanguage;

        return $this;
    }

    public function isShowCardNumbers(): bool
    {
        return $this->showCardNumbers;
    }

    public function setShowCardNumbers(bool $showCardNumbers): self
    {
        $this->showCardNumbers = $showCardNumbers;

        return $this;
    }

    public function isShowPrices(): bool
    {
        return $this->showPrices;
    }

    public function setShowPrices(bool $showPrices): self
    {
        $this->showPrices = $showPrices;

        return $this;
    }

    public function isEmailNotifications(): bool
    {
        return $this->emailNotifications;
    }

    public function setEmailNotifications(bool $emailNotifications): self
    {
        $this->emailNotifications = $emailNotifications;

        return $this;
    }

    public function isNewCardAlerts(): bool
    {
        return $this->newCardAlerts;
    }

    public function setNewCardAlerts(bool $newCardAlerts): self
    {
        $this->newCardAlerts = $newCardAlerts;

        return $this;
    }

    public function isPriceDropAlerts(): bool
    {
        return $this->priceDropAlerts;
    }

    public function setPriceDropAlerts(bool $priceDropAlerts): self
    {
        $this->priceDropAlerts = $priceDropAlerts;

        return $this;
    }

    public function isWeeklyReport(): bool
    {
        return $this->weeklyReport;
    }

    public function setWeeklyReport(bool $weeklyReport): self
    {
        $this->weeklyReport = $weeklyReport;

        return $this;
    }

    public function getProfileVisibility(): string
    {
        return $this->profileVisibility;
    }

    public function setProfileVisibility(string $profileVisibility): self
    {
        $this->profileVisibility = $profileVisibility;

        return $this;
    }

    public function isShowCollection(): bool
    {
        return $this->showCollection;
    }

    public function setShowCollection(bool $showCollection): self
    {
        $this->showCollection = $showCollection;

        return $this;
    }

    public function isShowWishlist(): bool
    {
        return $this->showWishlist;
    }

    public function setShowWishlist(bool $showWishlist): self
    {
        $this->showWishlist = $showWishlist;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
