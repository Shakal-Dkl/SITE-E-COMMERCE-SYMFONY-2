<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[]
     */
    public function findByPriceRange(?string $range): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC');

        if ($range === '10-29') {
            $queryBuilder
                ->andWhere('p.price >= :min')
                ->andWhere('p.price <= :max')
                ->setParameter('min', 10)
                ->setParameter('max', 29);
        }

        if ($range === '29-35') {
            $queryBuilder
                ->andWhere('p.price > :min')
                ->andWhere('p.price <= :max')
                ->setParameter('min', 29)
                ->setParameter('max', 35);
        }

        if ($range === '35-50') {
            $queryBuilder
                ->andWhere('p.price > :min')
                ->andWhere('p.price <= :max')
                ->setParameter('min', 35)
                ->setParameter('max', 50);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
