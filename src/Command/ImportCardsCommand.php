<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Booster;
use App\Entity\Card;
use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;
use Pokemon\Pokemon;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Pokemon::Options([
            'verify' => true,
            'timeout' => 120,
            'connect_timeout' => 30,
        ]);
        
        $apiKey = $_ENV['POKEMONTCG_API_KEY'] ?? null;
        Pokemon::ApiKey($apiKey);

        $setCode = 'sv8';
        $imported = 0;
        $maxRetries = 3;
        $retryCount = 0;
        /** @var array<\Pokemon\Card> $resp */
        $resp = [];

        $output->writeln("<info>Starting import for set: {$setCode}</info>");

        while ($retryCount < $maxRetries) {
            try {
                /** @var array<\Pokemon\Card> $resp */
                $resp = Pokemon::Card()->where(['set.id' => $setCode])->all();

                break; // Success, sortir de la boucle
            } catch (\Exception $e) {
                ++$retryCount;
                $output->writeln("<error>Error fetching cards (attempt {$retryCount}/{$maxRetries}): {$e->getMessage()}</error>");

                if ($retryCount < $maxRetries) {
                    $waitTime = $retryCount * 10; // Attendre 10, 20 secondes
                    $output->writeln("<comment>Retrying in {$waitTime} seconds...</comment>");
                    sleep($waitTime);
                } else {
                    $output->writeln('<error>Max retries reached. Please try again later.</error>');

                    return Command::FAILURE;
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
            assert($setData !== null);
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

            $this->em->persist($card);
            $this->em->flush();
            $this->em->clear();
            ++$imported;
        }

        $output->writeln("<info>{$imported} cards imported or updated.</info>");

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
            ->setAncientTrait($cardData->getAncientTrait() ?? null)
            ->setAbilities($this->objectsToArray($cardData->getAbilities()))
            ->setAttacks($this->objectsToArray($cardData->getAttacks()))
            ->setNationalPokedexNumbers($cardData->getNationalPokedexNumbers() ?? null)
            ->setTcgplayer($cardData->getTcgPlayer()?->toArray() ?? null)
            ->setCardmarket($cardData->getCardMarket()?->toArray() ?? null);

        // Download images and set paths
        $images = $cardData->getImages();
        $cardId = $cardData->getId();
        assert($images !== null && $cardId !== null);
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
        assert($setImages !== null);
        
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

        $set
            ->setId($setIdValue)
            ->setName($setName)
            ->setSeries($setData->getSeries() ?? null)
            ->setPrintedTotal($setData->getPrintedTotal() ?? null)
            ->setTotal($setData->getTotal() ?? null)
            ->setLegalities($setData->getLegalities()?->toArray() ?? null)
            ->setPtcgoCode($setData->getPtcgoCode() ?? null)
            ->setUpdatedAt($updatedAt ? new \DateTime((string) $updatedAt) : null)
            ->setReleaseDate($releaseDate ? new \DateTime((string) $releaseDate) : null)
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
     * @param mixed $items
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
}
