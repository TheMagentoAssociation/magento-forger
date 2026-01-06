<?php
/**
 * Created by Qoliber
 *
 * @category    Qoliber
 * @package     Qoliber_MagentoForger
 * @author      Jakub Winkler <jwinkler@qoliber.com>
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByGithubId(string $githubId): ?User
    {
        return $this->findOneBy(['githubId' => $githubId]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @return array<int, User>
     */
    public function findAllWithAffiliations(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.affiliations', 'a')
            ->addSelect('a')
            ->leftJoin('a.company', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getResult();
    }
}
