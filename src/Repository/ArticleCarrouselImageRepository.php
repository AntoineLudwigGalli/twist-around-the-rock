<?php

namespace App\Repository;

use App\Entity\ArticleCarrouselImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleCarrouselImage>
 *
 * @method ArticleCarrouselImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleCarrouselImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleCarrouselImage[]    findAll()
 * @method ArticleCarrouselImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleCarrouselImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleCarrouselImage::class);
    }

    public function save(ArticleCarrouselImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ArticleCarrouselImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ArticleCarrouselImage[] Returns an array of ArticleCarrouselImage objects
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

//    public function findOneBySomeField($value): ?ArticleCarrouselImage
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
