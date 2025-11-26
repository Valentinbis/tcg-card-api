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

        // Créer le QueryBuilder pour le comptage avant de définir la sélection complexe
        $countQb = clone $qb;

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
        ]);

        // Définir le paramètre user sur la requête principale seulement
        $qb->setParameter('user', $user);

        // Appliquer les filtres directement dans la requête

        // Filtre par type (JSON array contains)
        if ($type) {
            $qb->andWhere('c.types ? :type')
               ->setParameter('type', $type);
            $countQb->andWhere('c.types ? :type')
                    ->setParameter('type', $type);
        }

        // Filtre par rareté
        if ($rarity) {
            $qb->andWhere('c.rarity = :rarity')
               ->setParameter('rarity', $rarity);
            $countQb->andWhere('c.rarity = :rarity')
                    ->setParameter('rarity', $rarity);
        }

        // Filtre par set
        if ($setId) {
            $qb->andWhere('s.id = :setId')
               ->setParameter('setId', $setId);
            $countQb->andWhere('s.id = :setId')
                    ->setParameter('setId', $setId);
        }

        // Recherche par nom (nom français ou anglais)
        if ($search) {
            $qb->andWhere('(LOWER(c.nameFr) LIKE LOWER(:search) OR LOWER(c.name) LIKE LOWER(:search))')
               ->setParameter('search', '%' . $search . '%');
            $countQb->andWhere('(LOWER(c.nameFr) LIKE LOWER(:search) OR LOWER(c.name) LIKE LOWER(:search))')
                    ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par numéro
        if (null !== $number && '' !== $number) {
            $qb->andWhere('c.number = :number')
               ->setParameter('number', $number);
            $countQb->andWhere('c.number = :number')
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
                $countQb->andWhere('EXISTS (' . $ownedFilterSubQuery . ')');
            } else {
                $qb->andWhere('NOT EXISTS (' . $ownedFilterSubQuery . ')');
                $countQb->andWhere('NOT EXISTS (' . $ownedFilterSubQuery . ')');
            }
            // Le paramètre :user est utilisé dans la sous-requête, donc on le définit sur countQb aussi
            $countQb->setParameter('user', $user);
        }

        // Tri (seulement pour la requête principale)
        $qb->orderBy('c.' . $sort, $order);

        // Compter le total AVANT pagination
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

            // Décoder les champs JSON
            $nationalPokedexNumbers = $result['nationalPokedexNumbers'];
            if (is_string($nationalPokedexNumbers)) {
                $nationalPokedexNumbers = json_decode($nationalPokedexNumbers, true) ?? [];
            } elseif (!is_array($nationalPokedexNumbers)) {
                $nationalPokedexNumbers = [];
            }

            $images = $result['images'];
            if (is_string($images)) {
                $images = json_decode($images, true) ?? [];
            } elseif (!is_array($images)) {
                $images = [];
            }

            $dto = new CardViewDTO(
                $result['id'],
                $result['name'] ?? '',
                $result['nameFr'] ?? '',
                $result['number'] ?? '',
                $result['rarity'] ?? '',
                $nationalPokedexNumbers,
                $images,
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
                'v.cardmarket_average',
                'v.cardmarket_trend',
                'v.cardmarket_min',
                'v.cardmarket_max',
                'v.cardmarket_suggested',
                'v.cardmarket_germanProLow',
                'v.cardmarket_low_ex_plus',
                'v.cardmarket_avg1',
                'v.cardmarket_avg7',
                'v.cardmarket_avg30',
                'v.cardmarket_reverse',
                'v.cardmarket_reverse_low',
                'v.cardmarket_reverse_trend',
                'v.cardmarket_reverse_avg1',
                'v.cardmarket_reverse_avg7',
                'v.cardmarket_reverse_avg30',
                'v.cardmarket_holo',
                'v.tcgplayer_normal_low',
                'v.tcgplayer_normal_mid',
                'v.tcgplayer_normal_high',
                'v.tcgplayer_normal_market',
                'v.tcgplayer_normal_direct',
                'v.tcgplayer_reverse_low',
                'v.tcgplayer_reverse_mid',
                'v.tcgplayer_reverse_high',
                'v.tcgplayer_reverse_market',
                'v.tcgplayer_reverse_direct',
                'v.tcgplayer_holo_low',
                'v.tcgplayer_holo_mid',
                'v.tcgplayer_holo_high',
                'v.tcgplayer_holo_market',
                'v.tcgplayer_holo_direct',
            ])
            ->where('v.card = :cardId')
            ->setParameter('cardId', $cardId);

        $results = $qb->getQuery()->getScalarResult();

        $variants = [];
        foreach ($results as $result) {
            $variants[$result['type']] = [
                'price' => $result['price'],
                'cardmarket_average' => $result['cardmarket_average'],
                'cardmarket_trend' => $result['cardmarket_trend'],
                'cardmarket_min' => $result['cardmarket_min'],
                'cardmarket_max' => $result['cardmarket_max'],
                'cardmarket_suggested' => $result['cardmarket_suggested'],
                'cardmarket_germanProLow' => $result['cardmarket_germanProLow'],
                'cardmarket_low_ex_plus' => $result['cardmarket_low_ex_plus'],
                'cardmarket_avg1' => $result['cardmarket_avg1'],
                'cardmarket_avg7' => $result['cardmarket_avg7'],
                'cardmarket_avg30' => $result['cardmarket_avg30'],
                'cardmarket_reverse' => $result['cardmarket_reverse'],
                'cardmarket_reverse_low' => $result['cardmarket_reverse_low'],
                'cardmarket_reverse_trend' => $result['cardmarket_reverse_trend'],
                'cardmarket_reverse_avg1' => $result['cardmarket_reverse_avg1'],
                'cardmarket_reverse_avg7' => $result['cardmarket_reverse_avg7'],
                'cardmarket_reverse_avg30' => $result['cardmarket_reverse_avg30'],
                'cardmarket_holo' => $result['cardmarket_holo'],
                'tcgplayer_normal_low' => $result['tcgplayer_normal_low'],
                'tcgplayer_normal_mid' => $result['tcgplayer_normal_mid'],
                'tcgplayer_normal_high' => $result['tcgplayer_normal_high'],
                'tcgplayer_normal_market' => $result['tcgplayer_normal_market'],
                'tcgplayer_normal_direct' => $result['tcgplayer_normal_direct'],
                'tcgplayer_reverse_low' => $result['tcgplayer_reverse_low'],
                'tcgplayer_reverse_mid' => $result['tcgplayer_reverse_mid'],
                'tcgplayer_reverse_high' => $result['tcgplayer_reverse_high'],
                'tcgplayer_reverse_market' => $result['tcgplayer_reverse_market'],
                'tcgplayer_reverse_direct' => $result['tcgplayer_reverse_direct'],
                'tcgplayer_holo_low' => $result['tcgplayer_holo_low'],
                'tcgplayer_holo_mid' => $result['tcgplayer_holo_mid'],
                'tcgplayer_holo_high' => $result['tcgplayer_holo_high'],
                'tcgplayer_holo_market' => $result['tcgplayer_holo_market'],
                'tcgplayer_holo_direct' => $result['tcgplayer_holo_direct'],
            ];
        }

        return $variants;
    }
}
