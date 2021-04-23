<?php

namespace App\Repository;

use App\Entity\Timeline;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Timeline|null find($id, $lockMode = null, $lockVersion = null)
 * @method Timeline|null findOneBy(array $criteria, array $orderBy = null)
 * @method Timeline[]    findAll()
 * @method Timeline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimelineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Timeline::class);
    }

    /**
     * @return Array Returns an array with pairs of id 
     * and name for all timelines off an user
     */
    public function findAllByUser(int $userId)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT name, id FROM timeline t
            WHERE t.user_id = :user
            ORDER BY t.name ASC
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user' => $userId]);

        // https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#fetchallkeyvalue
        return $stmt->fetchAllKeyValue();
    }
}
