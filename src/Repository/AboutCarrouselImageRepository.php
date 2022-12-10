<?php

namespace App\Repository;

use App\Entity\AboutCarrouselImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AboutCarrouselImage>
 *
 * @method AboutCarrouselImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method AboutCarrouselImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method AboutCarrouselImage[]    findAll()
 * @method AboutCarrouselImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AboutCarrouselImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AboutCarrouselImage::class);
    }

    public function save(AboutCarrouselImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AboutCarrouselImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AboutCarrouselImage[] Returns an array of AboutCarrouselImage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AboutCarrouselImage
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
