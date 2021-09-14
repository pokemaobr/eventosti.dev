<?php

namespace App\Repository;

use App\Entity\Eventos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventosRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Eventos::class);
    }

    public function findByGreaterThanDataFim(\DateTime $agora)
    {

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery('
        Select e
        FROM App\Entity\Eventos e
        WHERE e.dataFim >= :agora
        ORDER BY e.dataFim ASC')
            ->setParameter('agora', $agora);

        return $query->getResult();

    }

    public function findByGreaterThanDataFimAndHabilitadoIsTrue(\DateTime $agora)
    {

        $entityManager = $this->getEntityManager();

        $hoje = new \DateTime();

        $query = $entityManager->createQuery('
        Select e
        FROM App\Entity\Eventos e
        WHERE e.habilitado = 1 AND e.dataFim >= :agora
        ORDER BY e.dataFim ASC')
            ->setParameter('agora', $agora);

        return $query->getResult();

    }

}
