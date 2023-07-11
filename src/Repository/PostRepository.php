<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllWithComments(): array
    {
        return $this->findAllQuery(withComments: true
        )->getQuery()->getResult();
    }

    public function findAllByAuthor(
        int|User $author
    ): array {
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )->where('p.author = :author')
            ->setParameter(
                'author',
                $author instanceof User ? $author->getId() : $author
            )->getQuery()->getResult();
    }

    public function findAllByAuthors(
        Collection|array $authors
    ): array {
        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )->where('p.author = (:authors)')
            ->setParameter(
                'authors',
                $authors instanceof User ? $authors->getId() : $authors
            )->getQuery()->getResult();
    }

    public function findAllWithMinLikes(int $minLikes): array
    {
        $idList = $this->findAllQuery(
            withLikes: true,
        )->select('p.id')
         ->groupBy('p.id')
         ->having('COUNT(l) >= :minLikes')
         ->setParameter('minLikes', $minLikes)
         ->getQuery()->getResult(Query::HYDRATE_SCALAR_COLUMN);

        return $this->findAllQuery(
            withComments: true,
            withLikes: true,
            withAuthors: true,
            withProfiles: true
        )->where('p.id in (:idList)')
            ->setParameter('idList', $idList)
            ->getQuery()->getResult();
    }

    private function findAllQuery(
        bool $withComments = false,
        bool $withLikes = false,
        bool $withAuthors = false,
        bool $withProfiles = false
    ): \Doctrine\ORM\QueryBuilder {
        $query = $this->createQueryBuilder('p');
        if ($withComments) {
            $query->leftJoin('p.comments', 'c')
                ->addSelect('c');
        }

        if ($withLikes) {
            $query->leftJoin('p.likedBy', 'l')
                ->addSelect('l');
        }

        if ($withAuthors || $withProfiles) {
            $query->leftJoin('p.author', 'a')
                ->addSelect('a');
        }

        if ($withProfiles) {
            $query->leftJoin('a.userProfile', 'up')
                ->addSelect('up');
        }

        return $query->orderBy('p.creationDate', 'DESC');
    }

    public function getAllPosts(): array
    {
        $qb = $this->createQueryBuilder('p');
         return $qb
             ->leftJoin('p.likedBy', 'l')
             ->addSelect('l')
             ->where('l > :amount')
             ->setParameter('amount', 1)
             ->getQuery()
             ->getResult();
    }

//    /**
//     * @return Post[] Returns an array of Post objects
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

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
