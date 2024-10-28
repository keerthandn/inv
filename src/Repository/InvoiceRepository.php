<?php
namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * @return Invoice[] Returns an array of Invoice objects
     */
    public function findAllInvoices(): array
    {
        return $this->findAll();
    }

    /**
     * @param int $id
     * @return Invoice|null Returns an Invoice object or null
     */
    public function findInvoiceById(int $id): ?Invoice
    {
        return $this->find($id);
    }

    /**
     * @param int $customerId
     * @return Invoice[] Returns an array of Invoices for a specific customer
     */
    public function findInvoicesByCustomerId(int $customerId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.customer = :customerId')
            ->setParameter('customerId', $customerId)
            ->orderBy('i.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Invoice[] Returns an array of Invoices within a date range
     */
    public function findInvoicesByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.date BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('i.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Invoice[] Returns an array of Invoices with a total amount greater than a specified value
     */
    public function findInvoicesAboveTotal(float $amount): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.total > :amount')
            ->setParameter('amount', $amount)
            ->getQuery()
            ->getResult();
    }

    // Additional custom query methods can be added here as needed
}
