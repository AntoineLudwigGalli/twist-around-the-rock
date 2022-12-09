<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{

    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Product::class);
        $this->paginator = $paginator;
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne les produits filtrés ou recherchés
     * @param SearchData $search
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function findSearch(SearchData $search) : \Knp\Component\Pager\Pagination\PaginationInterface
    {
        $query = $this
            ->createQueryBuilder('p')

        ;

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('p.name LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }

        if (!empty($search->min)) {
            $query = $query
                ->andWhere('p.price >= :min')
                ->setParameter('min', $search->min);
        }

        if (!empty($search->max)) {
            $query = $query
                ->andWhere('p.price <= :max')
                ->setParameter('max', $search->max);
        }

        if (!empty($search->available)) {
            $query = $query
                ->andWhere('p.available = 1');
        }

        if (!empty($search->categories)) {
            $query = $query
                ->select('c', 'p')
                ->join('p.category', 'c')
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->categories);
        }

        if (!empty($search->colors)) {
            $query = $query
                ->select('co', 'p')
                ->join('p.color', 'co')
                ->andWhere('co.id IN (:colors)')
                ->setParameter('colors', $search->colors);
        }

        if (!empty($search->stones)) {
            $query = $query
                ->select('s', 'p')
                ->join('p.stone', 's')
                ->andWhere('s.id IN (:stones)')
                ->setParameter('stones', $search->stones);
        }
        $query =  $query->getQuery();
        return $this->paginator->paginate(
            $query,
            $search->page,
            3
        );
    }
}