<?php

namespace App\Repository;

use App\Entity\Favoris;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Favoris|null find($id, $lockMode = null, $lockVersion = null)
 * @method Favoris|null findOneBy(array $criteria, array $orderBy = null)
 * @method Favoris[]    findAll()
 * @method Favoris[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavorisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favoris::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Favoris $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function supprimer($favoriId, $utilisateurId, bool $flush = true): void
    {
        $this->createQueryBuilder('favoris')
			->delete()
			->where('favoris.id_utilisateur = :utilisateurId')
			->andWhere('favoris.id_film = :filmId')
			->setParameter('filmId',$favoriId)
			->setParameter('utilisateurId',$utilisateurId)
			->getQuery()
			->getResult()
		;
    }

    /**
     * @return Favoris[] Returns an array of Favoris objects
     */
    public function getFavoris($utilisateurId)
    {
        return $this->createQueryBuilder('favoris')
			->select('film.id','film.titre','film.synopsis','film.acteur_principal')
			->innerJoin('App\Entity\Film', 'film', 'WITH', 'favoris.id_film = film.id')
			->where('favoris.id_utilisateur = :utilisateurId')
			->setParameter('utilisateurId',$utilisateurId)
			->getQuery()
			->getResult()
		;
    }

    /*
    public function findOneBySomeField($value): ?Favoris
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
