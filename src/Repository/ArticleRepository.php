<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function persist(Article $article)
    {
        $this->_em->persist($article);
    }

    public function flush()
    {
        $this->_em->flush();
    }

    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('article')
            ->select()  // czy przy sortowaniu całej tatabeli potrzebne jest select? i czy przy wybieraniu wszystkich kolumn pozostawiamy puste nawiasy czy tak jak w zapytaniach wstawiamy select('*') ?
            ->orderBy('article.pubDate', 'DESC')
            ->getQuery()
            //->getResult()     jak miałem to odkomentowane to rzucało błędem że przekazuje tablice zamiast obiektu
            ;
    }

//    /**
//     * @return Article[] Returns an array of Article objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
