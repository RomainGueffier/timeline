<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return Array Returns an array with pairs of id 
     * and name for all categories off an user
     */
    public function findAllByUser($userId)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT name, id FROM `category` c
            WHERE c.user_id = :user
            ORDER BY c.name ASC
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user' => $userId]);

        // https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#fetchallkeyvalue
        return $stmt->fetchAllKeyValue();
    }
}
