<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\Set;
use App\Entity\Booster;
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
        Pokemon::Options(['verify' => true]);
        Pokemon::ApiKey($_ENV['POKEMONTCG_API_KEY']);

        $setCode = 'sv8';
        $imported = 0;

        $resp = Pokemon::Card()->where(['set.id' => $setCode])->all();
        foreach ($resp as $data) {
            $id = $data->getId();
            $card = $this->em->getRepository(Card::class)->findOneBy([
                'number' => $data->getNumber(),
                'set' => $data->getSet()->getId(),
            ]);

            if (!$card) {
                $card = new Card();
                $card->setId($id);
                $output->writeln("<info>New card created: {$id}</info>");
            } else {
                $output->writeln("<comment>Card updated: {$id}</comment>");
            }

            $this->updateCardFromData($card, $data);

            $setId = $data->getSet()->getId();
            $set = $this->em->getRepository(Set::class)->find($setId);

            if (!$set) {
                $set = new Set();
                $set->setId($setId);
                $output->writeln("<info>New set created: {$setId}</info>");
            } else {
                $output->writeln("<comment>Set updated: {$setId}</comment>");
            }

            $this->updateSetFromData($set, $data->getSet(), $setId);
            $card->setSet($set);
            $this->em->persist($set);

            $series = $data->getSet()->getSeries();
            if ($series) {
                $boost = $this->em->getRepository(Booster::class)->find($series);

                if (!$boost) {
                    $boost = new Booster();
                    $boost->setName($series);
                    $output->writeln("<info>New booster created: {$series}</info>");
                } else {
                    $output->writeln("<comment>Booster updated: {$series}</comment>");
                }

                // $this->updateBoosterFromData($boost, $data->getSet()->getSeries(), $id);
                $this->em->persist($boost);
                $card->addBooster($boost);
            }

            $this->em->persist($card);
            $this->em->flush();
            $this->em->clear();
            $imported++;
        }

        $output->writeln("<info>{$imported} cards imported or updated.</info>");
        return Command::SUCCESS;
    }

    private function updateCardFromData(Card $card, $data): void
    {
        $card
            ->setName($data->getName())
            ->setSupertype($data->getSupertype() ?? null)
            ->setHp($data->getHp() ?? null)
            ->setFlavorText($data->getFlavorText() ?? null)
            ->setEvolvesFrom($data->getEvolvesFrom() ?? null)
            ->setNumber($data->getNumber() ?? null)
            ->setArtist($data->getArtist() ?? null)
            ->setRarity($data->getRarity() ?? null)
            ->setSubtypes($data->getSubtypes() ?? null)
            ->setTypes($data->getTypes() ?? null)
            ->setWeaknesses($this->objectsToArray($data->getWeaknesses()))
            ->setResistances($this->objectsToArray($data->getResistances()))
            ->setLegalities($data->getLegalities()?->toArray() ?? null)
            ->setRetreatCost($data->getRetreatCost() ?? null)
            ->setConvertedRetreatCost($data->getConvertedRetreatCost() ?? null)
            ->setEvolvesTo($data->getEvolvesTo() ?? null)
            ->setRules($data->getRules() ?? null)
            ->setAncientTrait($data->getAncientTrait() ?? null)
            ->setAbilities($this->objectsToArray($data->getAbilities()))
            ->setAttacks($this->objectsToArray($data->getAttacks()))
            ->setNationalPokedexNumbers($data->getNationalPokedexNumbers() ?? null)
            ->setTcgplayer($data->getTcgPlayer()?->toArray() ?? null)
            ->setCardmarket($data->getCardMarket()?->toArray() ?? null);

        // Download images and set paths
        $this->downloadImage($data->getImages()->getSmall(), 'public/images/cards/small/', $data->getId());
        $this->downloadImage($data->getImages()->getLarge(), 'public/images/cards/large/', $data->getId());
        $smallExt = $this->getImageExtension($data->getImages()->getSmall());
        $largeExt = $this->getImageExtension($data->getImages()->getLarge());

        $card->setImages([
            'small' => '/images/cards/small/' . $data->getId() . '.' . $smallExt,
            'large' => '/images/cards/large/' . $data->getId() . '.' . $largeExt,
        ]);
    }

    private function updateSetFromData(Set $set, $setData, string $setId): void
    {
        $this->downloadImage($setData->getImages()->getSymbol(), 'public/images/set/symbol/', $setId);
        $this->downloadImage($setData->getImages()->getLogo(), 'public/images/set/logo/', $setId);

        $symbolExt = $this->getImageExtension($setData->getImages()->getSymbol());
        $logoExt = $this->getImageExtension($setData->getImages()->getLogo());

        $set
            ->setId($setData->getId())
            ->setName($setData->getName())
            ->setSeries($setData->getSeries() ?? null)
            ->setPrintedTotal($setData->getPrintedTotal() ?? null)
            ->setTotal($setData->getTotal() ?? null)
            ->setLegalities($setData->getLegalities()?->toArray() ?? null)
            ->setPtcgoCode($setData->getPtcgoCode() ?? null)
            ->setUpdatedAt($setData->getUpdatedAt() ? new \DateTime($setData->getUpdatedAt()) : null)
            ->setReleaseDate($setData->getReleaseDate() ? new \DateTime($setData->getReleaseDate()) : null)
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
        return pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    }

    private function objectsToArray($items): ?array
    {
        if (!$items) {
            return null;
        }
        $result = [];
        foreach ($items as $item) {
            if (method_exists($item, 'toArray')) {
                $result[] = $item->toArray();
            }
        }
        return $result ?: null;
    }
}
