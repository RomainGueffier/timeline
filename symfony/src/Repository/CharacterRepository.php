<?php

namespace App\Repository;

use App\Entity\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Character|null find($id, $lockMode = null, $lockVersion = null)
 * @method Character|null findOneBy(array $criteria, array $orderBy = null)
 * @method Character[]    findAll()
 * @method Character[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    /**
     * @return Array Returns an array with pairs of id 
     * and name for all characters off an user
     */
    public function findAllByUser($userId)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT name, id FROM `character` c
            WHERE c.user_id = :user
            ORDER BY c.name ASC
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user' => $userId]);

        // https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#fetchallkeyvalue
        return $stmt->fetchAllKeyValue();
    }

    /**
     * @return Array Returns an array with data of characters to export in file
     * Do not export sensible data like ids !
     * !! Todo export images
     */
    public function exportByIds(array $charactersIds)
    {
        if (!$charactersIds) {
            return [];
        }
        
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT name, birth, birthplace, death, deathplace, age, description, weight, source FROM `character` c
            WHERE c.id IN (' . implode(',', $charactersIds) . ')
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#fetchallassociative
        return $stmt->fetchAllAssociative();
    }
}
