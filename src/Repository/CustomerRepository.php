<?php
namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @return Customer[] Returns an array of Customer objects
     */
    public function findAllCustomers(): array
    {
        return $this->findAll();
    }

    /**
     * @param int $id
     * @return Customer|null Returns a Customer object or null
     */
    public function findCustomerById(int $id): ?Customer
    {
        return $this->find($id);
    }

    /**
     * @param string $email
     * @return Customer|null Returns a Customer object or null
     */
    public function findCustomerByEmail(string $email): ?Customer
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Customer[] Returns an array of Customer objects
     */
    public function findActiveCustomers(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    // Example of a custom query method
    public function searchCustomers(string $searchTerm): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.name LIKE :searchTerm OR c.email LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }
}
