<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Array Returns an array with pairs of id 
     * and name for all events off an user
     */
    public function findAllByUser($userId)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT name, id FROM `event` e
            WHERE e.user_id = :user
            ORDER BY e.name ASC
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user' => $userId]);

        // https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#fetchallkeyvalue
        return $stmt->fetchAllKeyValue();
    }
}
