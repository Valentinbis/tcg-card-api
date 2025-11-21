<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Booster;
use App\Entity\Card;
use App\Entity\CardVariant;
use App\Entity\Set;
use App\Enum\CardVariantEnum;
use Doctrine\ORM\EntityManagerInterface;
use Pokemon\Pokemon;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-cards',
    description: 'Import cards from pokemontcg.io API into the database'
)]
class ImportCardsCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->addOption(
            'truncate',
            't',
            InputOption::VALUE_NONE,
            'Truncate tables before importing'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Pokemon::Options([
            'verify' => true,
            'timeout' => 120,
            'connect_timeout' => 30,
        ]);

        $apiKey = $_ENV['POKEMONTCG_API_KEY'] ?? null;
        Pokemon::ApiKey($apiKey);

        if ($input->getOption('truncate')) {
            $output->writeln('<info>Truncating tables...</info>');
            $schemaManager = $this->em->getConnection()->createSchemaManager();
            $tables = ['card_variant', 'cards', 'sets', 'boosters'];
            foreach ($tables as $table) {
                if ($schemaManager->tablesExist([$table])) {
                    $this->em->getConnection()->executeStatement("TRUNCATE TABLE {$table} CASCADE;");
                    $output->writeln("<info>Truncated table: {$table}</info>");
                } else {
                    $output->writeln("<warning>Table {$table} does not exist, skipping truncate.</warning>");
                }
            }
            $output->writeln('<info>Tables truncation completed.</info>');
        }

        $output->writeln('<info>Fetching all sets...</info>');
        $maxRetries = 3;
        $retryCount = 0;
        $sets = [];

        while ($retryCount < $maxRetries) {
            try {
                $sets = Pokemon::Set()->all();

                break; // Success
            } catch (\Exception $e) {
                ++$retryCount;
                $output->writeln("<error>Error fetching sets (attempt {$retryCount}/{$maxRetries}): {$e->getMessage()}</error>");

                if ($retryCount < $maxRetries) {
                    $waitTime = $retryCount * 10; // Wait 10, 20 seconds
                    $output->writeln("<comment>Retrying in {$waitTime} seconds...</comment>");
                    sleep($waitTime);
                } else {
                    $output->writeln('<error>Max retries reached for fetching sets. Aborting.</error>');

                    return Command::FAILURE;
                }
            }
        }

        $output->writeln('<info>Found '.count($sets).' sets to import.</info>');

        // Inverser l'ordre d'importation des sets
        $sets = array_reverse($sets);

        $totalImported = 0;
        foreach ($sets as $setData) {
            $setCode = $setData->getId();
            $output->writeln("<info>Starting import for set: {$setCode}</info>");

            $imported = 0;
            $maxRetries = 3;
            $retryCount = 0;
            /** @var array<\Pokemon\Card> $resp */
            $resp = [];

            while ($retryCount < $maxRetries) {
                try {
                    /** @var array<\Pokemon\Card> $resp */
                    $resp = Pokemon::Card()->where(['set.id' => $setCode])->all();

                    break; // Success, sortir de la boucle
                } catch (\Exception $e) {
                    ++$retryCount;
                    $output->writeln("<error>Error fetching cards for set {$setCode} (attempt {$retryCount}/{$maxRetries}): {$e->getMessage()}</error>");

                    if ($retryCount < $maxRetries) {
                        $waitTime = $retryCount * 10; // Attendre 10, 20 secondes
                        $output->writeln("<comment>Retrying in {$waitTime} seconds...</comment>");
                        sleep($waitTime);
                    } else {
                        $output->writeln("<error>Max retries reached for set {$setCode}. Skipping.</error>");

                        continue 2; // Skip to next set
                    }
                }
            }

            foreach ($resp as $cardData) {
                $id = $cardData->getId();
                assert(is_string($id));

                $card = $this->em->getRepository(Card::class)->findOneBy([
                    'number' => $cardData->getNumber(),
                    'set' => $cardData->getSet()?->getId(),
                ]);

                if (!$card) {
                    $card = new Card();
                    $card->setId($id);
                    $output->writeln("<info>New card created: {$id}</info>");
                } else {
                    $output->writeln("<comment>Card updated: {$id}</comment>");
                }

                $this->updateCardFromData($card, $cardData);

                $setData = $cardData->getSet();
                assert(null !== $setData);
                $setId = $setData->getId();
                assert(is_string($setId));

                $set = $this->em->getRepository(Set::class)->find($setId);

                if (!$set) {
                    $set = new Set();
                    $set->setId($setId);
                    $output->writeln("<info>New set created: {$setId}</info>");
                } else {
                    $output->writeln("<comment>Set updated: {$setId}</comment>");
                }

                $this->updateSetFromData($set, $setData, $setId);
                $card->setSet($set);
                $this->em->persist($set);

                $series = $setData->getSeries();
                if ($series) {
                    assert(is_string($series));
                    $boost = $this->em->getRepository(Booster::class)->find($series);

                    if (!$boost) {
                        $boost = new Booster();
                        $boost->setName($series);
                        $output->writeln("<info>New booster created: {$series}</info>");
                    } else {
                        $output->writeln("<comment>Booster updated: {$series}</comment>");
                    }

                    $this->em->persist($boost);
                    $card->addBooster($boost);
                }

                // Création des variantes existantes uniquement
                $cardmarket = $cardData->getCardMarket()?->toArray() ?? [];
                $tcgplayer = $cardData->getTcgPlayer()?->toArray() ?? [];
                $cmPrices = $cardmarket['prices'] ?? [];
                $tpPrices = $tcgplayer['prices'] ?? [];

                // Normal
                $normalTP = $tpPrices['normal'] ?? [];
                $variant = new CardVariant();
                $variant->setCard($card);
                $variant->setType(CardVariantEnum::NORMAL);
                $variant->setCardmarketAverage($cmPrices['averageSellPrice'] ?? $normalTP['market'] ?? null);
                $variant->setCardmarketTrend($cmPrices['trendPrice'] ?? null);
                $variant->setCardmarketMin($cmPrices['lowPrice'] ?? null);
                $variant->setCardmarketMax($cmPrices['highPrice'] ?? null);
                $variant->setCardmarketSuggested($cmPrices['suggestedPrice'] ?? null);
                $variant->setCardmarketGermanProLow($cmPrices['germanProLow'] ?? null);
                $variant->setCardmarketLowExPlus($cmPrices['lowPriceExPlus'] ?? null);
                $variant->setCardmarketAvg1($cmPrices['avg1'] ?? null);
                $variant->setCardmarketAvg7($cmPrices['avg7'] ?? null);
                $variant->setCardmarketAvg30($cmPrices['avg30'] ?? null);
                $variant->setTcgplayerNormalLow($normalTP['low'] ?? null);
                $variant->setTcgplayerNormalMid($normalTP['mid'] ?? null);
                $variant->setTcgplayerNormalHigh($normalTP['high'] ?? null);
                $variant->setTcgplayerNormalMarket($normalTP['market'] ?? null);
                $variant->setTcgplayerNormalDirect($normalTP['directLow'] ?? null);
                $variant->setPrice($variant->getCardmarketAverage());
                $this->em->persist($variant);
                $card->addVariant($variant);

                // Reverse
                $reverseTP = $tpPrices['reverseHolofoil'] ?? [];
                $reversePrice = $cmPrices['reverseHoloSell'] ?? $reverseTP['market'] ?? null;
                if (null !== $reversePrice) {
                    $variant = new CardVariant();
                    $variant->setCard($card);
                    $variant->setType(CardVariantEnum::REVERSE);
                    $variant->setCardmarketReverse($cmPrices['reverseHoloSell'] ?? $reverseTP['market'] ?? null);
                    $variant->setCardmarketReverseLow($cmPrices['reverseHoloLow'] ?? null);
                    $variant->setCardmarketReverseTrend($cmPrices['reverseHoloTrend'] ?? null);
                    $variant->setCardmarketReverseAvg1($cmPrices['reverseHoloAvg1'] ?? null);
                    $variant->setCardmarketReverseAvg7($cmPrices['reverseHoloAvg7'] ?? null);
                    $variant->setCardmarketReverseAvg30($cmPrices['reverseHoloAvg30'] ?? null);
                    $variant->setTcgplayerReverseLow($reverseTP['low'] ?? null);
                    $variant->setTcgplayerReverseMid($reverseTP['mid'] ?? null);
                    $variant->setTcgplayerReverseHigh($reverseTP['high'] ?? null);
                    $variant->setTcgplayerReverseMarket($reverseTP['market'] ?? null);
                    $variant->setTcgplayerReverseDirect($reverseTP['directLow'] ?? null);
                    $variant->setPrice($variant->getCardmarketReverse());
                    $this->em->persist($variant);
                    $card->addVariant($variant);
                }

                // Holo
                $holoTP = $tpPrices['holofoil'] ?? [];
                $holoPrice = $cmPrices['holoSell'] ?? $holoTP['market'] ?? null;
                if (null !== $holoPrice) {
                    $variant = new CardVariant();
                    $variant->setCard($card);
                    $variant->setType(CardVariantEnum::HOLO);
                    $variant->setCardmarketHolo($cmPrices['holoSell'] ?? $holoTP['market'] ?? null);
                    $variant->setTcgplayerHoloLow($holoTP['low'] ?? null);
                    $variant->setTcgplayerHoloMid($holoTP['mid'] ?? null);
                    $variant->setTcgplayerHoloHigh($holoTP['high'] ?? null);
                    $variant->setTcgplayerHoloMarket($holoTP['market'] ?? null);
                    $variant->setTcgplayerHoloDirect($holoTP['directLow'] ?? null);
                    $variant->setPrice($variant->getCardmarketHolo());
                    $this->em->persist($variant);
                    $card->addVariant($variant);
                }

                $this->em->persist($card);
                ++$imported;
            }

            $this->em->flush();
            $this->em->clear();
            $totalImported += $imported;
            $output->writeln("<info>{$imported} cards imported or updated for set {$setCode}.</info>");
        }

        $output->writeln("<info>Total cards imported: {$totalImported}</info>");

        return Command::SUCCESS;
    }

    /**
     * @param \Pokemon\Card $cardData
     */
    private function updateCardFromData(Card $card, $cardData): void
    {
        $card
            ->setName((string) ($cardData->getName() ?? ''))
            ->setSupertype($cardData->getSupertype() ?? null)
            ->setHp($cardData->getHp() ?? null)
            ->setFlavorText($cardData->getFlavorText() ?? null)
            ->setEvolvesFrom($cardData->getEvolvesFrom() ?? null)
            ->setNumber($cardData->getNumber() ?? null)
            ->setArtist($cardData->getArtist() ?? null)
            ->setRarity($cardData->getRarity() ?? null)
            ->setSubtypes($cardData->getSubtypes() ?? null)
            ->setTypes($cardData->getTypes() ?? null)
            ->setWeaknesses($this->objectsToArray($cardData->getWeaknesses()))
            ->setResistances($this->objectsToArray($cardData->getResistances()))
            ->setLegalities($cardData->getLegalities()?->toArray() ?? null)
            ->setRetreatCost($cardData->getRetreatCost() ?? null)
            ->setConvertedRetreatCost($cardData->getConvertedRetreatCost() ?? null)
            ->setEvolvesTo($cardData->getEvolvesTo() ?? null)
            ->setRules($cardData->getRules() ?? null)
            ->setAncientTrait($cardData->getAncientTrait()?->toArray() ?? null)
            ->setAbilities($this->objectsToArray($cardData->getAbilities()))
            ->setAttacks($this->objectsToArray($cardData->getAttacks()))
            ->setNationalPokedexNumbers($cardData->getNationalPokedexNumbers() ?? null);

        // Download images and set paths
        $images = $cardData->getImages();
        $cardId = $cardData->getId();
        assert(null !== $images && null !== $cardId);
        assert(is_string($cardId));

        $smallUrl = $images->getSmall();
        $largeUrl = $images->getLarge();
        assert(is_string($smallUrl) && is_string($largeUrl));

        $this->downloadImage($smallUrl, 'public/images/cards/small/', $cardId);
        $this->downloadImage($largeUrl, 'public/images/cards/large/', $cardId);
        $smallExt = $this->getImageExtension($smallUrl);
        $largeExt = $this->getImageExtension($largeUrl);

        $card->setImages([
            'small' => "/images/cards/small/{$cardId}.{$smallExt}",
            'large' => "/images/cards/large/{$cardId}.{$largeExt}",
        ]);
    }

    /**
     * @param \Pokemon\Set $setData
     */
    private function updateSetFromData(Set $set, $setData, string $setId): void
    {
        $setImages = $setData->getImages();
        assert(null !== $setImages);

        $symbolUrl = $setImages->getSymbol();
        $logoUrl = $setImages->getLogo();
        assert(is_string($symbolUrl) && is_string($logoUrl));

        $this->downloadImage($symbolUrl, 'public/images/set/symbol/', $setId);
        $this->downloadImage($logoUrl, 'public/images/set/logo/', $setId);

        $symbolExt = $this->getImageExtension($symbolUrl);
        $logoExt = $this->getImageExtension($logoUrl);

        $setIdValue = $setData->getId();
        $setName = $setData->getName();
        assert(is_string($setIdValue) && is_string($setName));

        $updatedAt = $setData->getUpdatedAt();
        $releaseDate = $setData->getReleaseDate();

        // Corriger les dates malformées (années à 3 chiffres)
        $updatedAtFixed = $updatedAt ? $this->fixMalformedDate((string) $updatedAt) : null;
        $releaseDateFixed = $releaseDate ? $this->fixMalformedDate((string) $releaseDate) : null;

        $set
            ->setId($setIdValue)
            ->setName($setName)
            ->setSeries($setData->getSeries() ?? null)
            ->setPrintedTotal($setData->getPrintedTotal() ?? null)
            ->setTotal($setData->getTotal() ?? null)
            ->setLegalities($setData->getLegalities()?->toArray() ?? null)
            ->setPtcgoCode($setData->getPtcgoCode() ?? null)
            ->setUpdatedAt($updatedAtFixed ? new \DateTime($updatedAtFixed) : null)
            ->setReleaseDate($releaseDateFixed ? new \DateTime($releaseDateFixed) : null)
            ->setImages([
                'symbol' => "/images/set/symbol/{$setId}.{$symbolExt}",
                'logo' => "/images/set/logo/{$setId}.{$logoExt}",
            ]);
    }

    private function downloadImage(string $url, string $dir, string $name): void
    {
        if (!$url) {
            return;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Détecter l'extension à partir de l'URL (par défaut .jpg si non trouvée)
        $extension = $this->getImageExtension($url);
        $filePath = "$dir/$name.$extension";

        if (!file_exists($filePath)) {
            file_put_contents($filePath, @file_get_contents($url));
        }
    }

    private function getImageExtension(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return pathinfo(is_string($path) ? $path : '', PATHINFO_EXTENSION) ?: 'jpg';
    }

    /**
     * @return array<mixed>|null
     */
    private function objectsToArray($items): ?array
    {
        if (!$items) {
            return null;
        }
        if (!is_iterable($items)) {
            return null;
        }
        $result = [];
        foreach ($items as $item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                $result[] = $item->toArray();
            }
        }

        return $result ?: null;
    }

    /**
     * Corrige les dates malformées et valide leur format
     * Gère notamment les années à 3 chiffres et autres formats invalides.
     */
    private function fixMalformedDate(string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        // Vérifier si la date commence par une année à 3 chiffres (comme "025")
        if (preg_match('/^(\d{3})\/(\d{2})\/(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $dateString, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            $hour = (int) $matches[4];
            $minute = (int) $matches[5];
            $second = (int) $matches[6];

            // Ajouter "20" au début de l'année pour corriger (025 -> 2025)
            $correctedYear = 2000 + $year;

            // Valider que la date est correcte
            if ($this->isValidDate($correctedYear, $month, $day, $hour, $minute, $second)) {
                return sprintf('%04d/%02d/%02d %02d:%02d:%02d', $correctedYear, $month, $day, $hour, $minute, $second);
            }
        }

        // Essayer de parser avec DateTime pour d'autres formats potentiellement valides
        try {
            $dateTime = new \DateTime($dateString);

            return $dateTime->format('Y/m/d H:i:s');
        } catch (\Exception $e) {
            // Si la date ne peut pas être parsée, retourner null
            return null;
        }
    }

    /**
     * Valide qu'une date est correcte.
     */
    private function isValidDate(int $year, int $month, int $day, int $hour, int $minute, int $second): bool
    {
        return checkdate($month, $day, $year)
               && $hour >= 0 && $hour <= 23
               && $minute >= 0 && $minute <= 59
               && $second >= 0 && $second <= 59;
    }
}
