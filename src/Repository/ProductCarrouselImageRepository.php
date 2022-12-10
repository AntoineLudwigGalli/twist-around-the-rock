<?php

namespace App\Repository;

use App\Entity\ProductCarrouselImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductCarrouselImage>
 *
 * @method ProductCarrouselImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductCarrouselImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductCarrouselImage[]    findAll()
 * @method ProductCarrouselImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductCarrouselImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCarrouselImage::class);
    }

    public function save(ProductCarrouselImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductCarrouselImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ProductCarrouselImage[] Returns an array of ProductCarrouselImage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProductCarrouselImage
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
