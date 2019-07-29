<?php

namespace App\Repository;

use App\Entity\Contacttypes;
use App\Entity\Members;
use App\Entity\Dealers;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Dealers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dealers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dealers[]    findAll()
 * @method Dealers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DealersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Dealers::class);
    }



    public function findForSelect($showroomid,$countrycode=false)
    {
        return $this->queryForSelect($showroomid,$countrycode)->execute();
    }

    public function queryForSelect($showroomid,$countrycode=false)
    {
        $entityManager = $this->getEntityManager();

        $dql = 'SELECT d
        FROM App\Entity\Dealers d
        JOIN d.showrooms s
        WHERE s.showroomid = :showroomid';
        if($countrycode)
        {
            $dql .=' AND d.countryisocode = :countryisocode';
        }
        $query = $entityManager->createQuery(
            $dql
        );
        $query->setParameter('showroomid',$showroomid);
        if($countrycode){
            $query->setParameter('countryisocode',$countrycode);
        }
        return $query;
    }

    public function findRegistrations()
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT d.dealerid,d.dealercode,d.dealername,u.email
        FROM App\Entity\Dealers d
        JOIN d.users u
        JOIN u.contacttypes ct
        WHERE d.enabled = \'N\' AND d.dealerid>1 AND d.deniedflag = \'N\'
        AND (
            ct.contacttype = \'OTHER\' OR
            ct.contacttype = \'PRINCIPAL\'
        )
        ORDER BY d.dealername ASC'
        );
        return $query->execute();
    }

}
