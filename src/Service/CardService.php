<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CardViewDTO;
use App\Entity\Card;
use App\Entity\Collection;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CardService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return array{data: array<CardViewDTO>, total: int}
     */
    public function getUserCardsWithFilters(
        User $user,
        ?string $type,
        ?string $rarity,
        ?string $setId,
        ?string $search,
        ?string $number,
        ?string $owned,
        int $offset,
        int $limit,
        string $sort,
        string $order
    ): array {
        // Construction de la requête optimisée avec sélection partielle
        $qb = $this->em->getRepository(Card::class)->createQueryBuilder('c')
            ->leftJoin('c.set', 's');

        // Sous-requête pour déterminer si la carte est possédée par l'utilisateur
        $ownedSubQuery = $this->em->createQueryBuilder()
            ->select('1')
            ->from(Collection::class, 'col')
            ->where('col.cardId = c.id')
            ->andWhere('col.user = :user')
            ->getDQL();

        // Sélection partielle pour éviter de charger toutes les données en mémoire
        $qb->select([
            'c.id',
            'c.name',
            'c.nameFr',
            'c.number',
            'c.rarity',
            'c.nationalPokedexNumbers',
            'c.images',
            'c.types',
            's.id as set_id',
            's.name as set_name',
            'CASE WHEN EXISTS (' . $ownedSubQuery . ') THEN true ELSE false END as owned'
        ])
        ->setParameter('user', $user);

        // Appliquer les filtres directement dans la requête

        // Filtre par type (JSON array contains)
        if ($type) {
            $qb->andWhere('c.types ? :type')
               ->setParameter('type', $type);
        }

        // Filtre par rareté
        if ($rarity) {
            $qb->andWhere('c.rarity = :rarity')
               ->setParameter('rarity', $rarity);
        }

        // Filtre par set
        if ($setId) {
            $qb->andWhere('s.id = :setId')
               ->setParameter('setId', $setId);
        }

        // Recherche par nom (nom français ou anglais)
        if ($search) {
            $qb->andWhere('(LOWER(c.nameFr) LIKE LOWER(:search) OR LOWER(c.name) LIKE LOWER(:search))')
               ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par numéro
        if (null !== $number && '' !== $number) {
            $qb->andWhere('c.number = :number')
               ->setParameter('number', $number);
        }

        // Filtre owned - utilise la sous-requête EXISTS
        if (null !== $owned) {
            $ownedFilterSubQuery = $this->em->createQueryBuilder()
                ->select('1')
                ->from(Collection::class, 'col2')
                ->where('col2.cardId = c.id')
                ->andWhere('col2.user = :user')
                ->getDQL();

            if ('true' === $owned) {
                $qb->andWhere('EXISTS (' . $ownedFilterSubQuery . ')');
            } else {
                $qb->andWhere('NOT EXISTS (' . $ownedFilterSubQuery . ')');
            }
            $qb->setParameter('user', $user);
        }

        // Tri
        $qb->orderBy('c.' . $sort, $order);

        // Compter le total AVANT pagination
        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT c.id)');
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        // Appliquer la pagination
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        // Récupérer les résultats sous forme de tableau
        $results = $qb->getQuery()->getScalarResult();

        // Construire les DTOs directement depuis les résultats scalaires
        $cardViews = [];
        foreach ($results as $result) {
            // Récupérer les variantes de manière optimisée
            $variants = $this->getCardVariantsOptimized($result['id']);

            $dto = new CardViewDTO(
                $result['id'],
                $result['name'] ?? '',
                $result['nameFr'] ?? '',
                $result['number'] ?? '',
                $result['rarity'] ?? '',
                $result['nationalPokedexNumbers'] ?? [],
                $result['images'] ?? [],
                (bool) $result['owned']
            );

            $dto->variants = $variants;
            $cardViews[] = $dto;
        }

        return [
            'data' => $cardViews,
            'total' => $total,
        ];
    }

    /**
     * @return array<array{id: string, name: string}>
     */
    public function getAllSets(): array
    {
        $qb = $this->em->getRepository(\App\Entity\Set::class)->createQueryBuilder('s')
            ->orderBy('s.releaseDate', 'DESC');

        /** @var array<\App\Entity\Set> $sets */
        $sets = $qb->getQuery()->getResult();

        return array_map(
            fn (\App\Entity\Set $set): array => [
                'id' => $set->getId(),
                'name' => $set->getName() ?? '',
            ],
            $sets
        );
    }

    public function getCardById(string $cardId): ?Card
    {
        return $this->em->getRepository(Card::class)->find($cardId);
    }

    /**
     * Récupère les variantes d'une carte de manière optimisée
     * @return array<string, array<string, mixed>>
     */
    private function getCardVariantsOptimized(string $cardId): array
    {
        $qb = $this->em->getRepository(\App\Entity\CardVariant::class)->createQueryBuilder('v')
            ->select([
                'v.type',
                'v.price',
                'v.cardmarketAverage',
                'v.cardmarketTrend',
                'v.cardmarketMin',
                'v.cardmarketMax',
                'v.cardmarketSuggested',
                'v.cardmarketGermanProLow',
                'v.cardmarketLowExPlus',
                'v.cardmarketAvg1',
                'v.cardmarketAvg7',
                'v.cardmarketAvg30',
                'v.cardmarketReverse',
                'v.cardmarketReverseLow',
                'v.cardmarketReverseTrend',
                'v.cardmarketReverseAvg1',
                'v.cardmarketReverseAvg7',
                'v.cardmarketReverseAvg30',
                'v.cardmarketHolo',
                'v.tcgplayerNormalLow',
                'v.tcgplayerNormalMid',
                'v.tcgplayerNormalHigh',
                'v.tcgplayerNormalMarket',
                'v.tcgplayerNormalDirect',
                'v.tcgplayerReverseLow',
                'v.tcgplayerReverseMid',
                'v.tcgplayerReverseHigh',
                'v.tcgplayerReverseMarket',
                'v.tcgplayerReverseDirect',
                'v.tcgplayerHoloLow',
                'v.tcgplayerHoloMid',
                'v.tcgplayerHoloHigh',
                'v.tcgplayerHoloMarket',
                'v.tcgplayerHoloDirect',
            ])
            ->where('v.card = :cardId')
            ->setParameter('cardId', $cardId);

        $results = $qb->getQuery()->getScalarResult();

        $variants = [];
        foreach ($results as $result) {
            $variants[$result['type']] = [
                'price' => $result['price'],
                'cardmarket_average' => $result['cardmarketAverage'],
                'cardmarket_trend' => $result['cardmarketTrend'],
                'cardmarket_min' => $result['cardmarketMin'],
                'cardmarket_max' => $result['cardmarketMax'],
                'cardmarket_suggested' => $result['cardmarketSuggested'],
                'cardmarket_germanProLow' => $result['cardmarketGermanProLow'],
                'cardmarket_low_ex_plus' => $result['cardmarketLowExPlus'],
                'cardmarket_avg1' => $result['cardmarketAvg1'],
                'cardmarket_avg7' => $result['cardmarketAvg7'],
                'cardmarket_avg30' => $result['cardmarketAvg30'],
                'cardmarket_reverse' => $result['cardmarketReverse'],
                'cardmarket_reverse_low' => $result['cardmarketReverseLow'],
                'cardmarket_reverse_trend' => $result['cardmarketReverseTrend'],
                'cardmarket_reverse_avg1' => $result['cardmarketReverseAvg1'],
                'cardmarket_reverse_avg7' => $result['cardmarketReverseAvg7'],
                'cardmarket_reverse_avg30' => $result['cardmarketReverseAvg30'],
                'cardmarket_holo' => $result['cardmarketHolo'],
                'tcgplayer_normal_low' => $result['tcgplayerNormalLow'],
                'tcgplayer_normal_mid' => $result['tcgplayerNormalMid'],
                'tcgplayer_normal_high' => $result['tcgplayerNormalHigh'],
                'tcgplayer_normal_market' => $result['tcgplayerNormalMarket'],
                'tcgplayer_normal_direct' => $result['tcgplayerNormalDirect'],
                'tcgplayer_reverse_low' => $result['tcgplayerReverseLow'],
                'tcgplayer_reverse_mid' => $result['tcgplayerReverseMid'],
                'tcgplayer_reverse_high' => $result['tcgplayerReverseHigh'],
                'tcgplayer_reverse_market' => $result['tcgplayerReverseMarket'],
                'tcgplayer_reverse_direct' => $result['tcgplayerReverseDirect'],
                'tcgplayer_holo_low' => $result['tcgplayerHoloLow'],
                'tcgplayer_holo_mid' => $result['tcgplayerHoloMid'],
                'tcgplayer_holo_high' => $result['tcgplayerHoloHigh'],
                'tcgplayer_holo_market' => $result['tcgplayerHoloMarket'],
                'tcgplayer_holo_direct' => $result['tcgplayerHoloDirect'],
            ];
        }

        return $variants;
    }
}
