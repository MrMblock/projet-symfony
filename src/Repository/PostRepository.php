<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Post> */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function findAllOrderedByPriority(): array
    {
        return $this->findAllOrderedByPriorityQuery()
            ->getResult()
        ;
    }

    public function findAllOrderedByPriorityQuery(): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.priority', 'DESC')
            ->addOrderBy('p.publishedAt', 'DESC')
            ->getQuery();
    }

    /** @return Post[] */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->where('LOWER(p.title) LIKE :q')
            ->orWhere('LOWER(p.content) LIKE :q')
            ->orWhere('LOWER(c.name) LIKE :q')
            ->setParameter('q', '%'.mb_strtolower($query).'%')
            ->orderBy('p.priority', 'DESC')
            ->addOrderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
