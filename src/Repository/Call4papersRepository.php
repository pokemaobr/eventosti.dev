<?php

namespace App\Repository;

use App\Entity\Call4papers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Call4papersRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Call4papers::class);
    }

    public function pegarCall4PapersAbertos(\DateTime $agora)
    {

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
        Select e
        FROM App\Entity\Call4papers e
        WHERE e.dataEncerramento >= :agora
        ORDER BY e.dataEncerramento ASC')
            ->setParameter('agora', $agora);

        return $query->getResult();

    }

    public function pegarCall4PapersAbertosHabilitados(\DateTime $agora)
    {

        $entityManager = $this->getEntityManager();

        $hoje = new \DateTime();

        $query = $entityManager->createQuery('
        Select e
        FROM App\Entity\Call4papers e
        WHERE e.habilitado = 1 AND e.dataEncerramento >= :agora
        ORDER BY e.dataEncerramento ASC')
            ->setParameter('agora', $agora);

        return $query->getResult();

    }

}
