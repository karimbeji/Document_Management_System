<?php

namespace GedBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * VersionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VersionRepository extends EntityRepository
{
    public function findVersionsByFile($id)
    {
        $qb = $this->createQueryBuilder('ve')
            ->select('ve')
            ->where('ve.file = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()
                ->getResult();
    }

    public function findCreators($id)
    {
        $qb = $this->_em->createQueryBuilder()
            ->from('GedBundle:Version', 've')
            ->from('GedBundle:User', 'usr')
            ->select('usr')
            ->distinct()
            ->where('ve.createdBy = usr.id')
            ->andWhere('ve.file = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()
            ->getResult();
    }

}
