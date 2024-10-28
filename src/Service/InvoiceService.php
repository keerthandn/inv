<?php
namespace App\Service;

use App\Entity\Customer;
use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceService
{
    private const TAX_RATE = 0.20; // 20% tax rate

    private $entityManager;
    private $cartService;

    public function __construct(
        EntityManagerInterface $entityManager,
        CartService $cartService
    ) {
        $this->entityManager = $entityManager;
        $this->cartService = $cartService;
    }

    public function createInvoice(Customer $customer, string $paymentMethod, float $flatDiscount = 0.0): Invoice
    {
        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            throw new \InvalidArgumentException('Cannot create invoice with empty cart');
        }

        $invoice = new Invoice();
        $invoice->setCustomer($customer);
        $invoice->setItems($cart);
        $invoice->setPaymentMethod($paymentMethod);
        
        $this->calculateTotals($invoice, $flatDiscount);
        
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        
        $this->cartService->clearCart();
        
        return $invoice;
    }

    private function calculateTotals(Invoice $invoice, float $flatDiscount): void
    {
        $items = $invoice->getItems();
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $invoice->setSubtotal($subtotal);
        $invoice->setDiscount($flatDiscount);
        
        $taxableAmount = $subtotal - $flatDiscount;
        $tax = $taxableAmount * self::TAX_RATE;
        $invoice->setTax($tax);
        
        $total = $taxableAmount + $tax;
        $invoice->setTotal($total);
    }

    public function getInvoiceById(int $id): ?Invoice
    {
        return $this->entityManager->getRepository(Invoice::class)->find($id);
    }

    public function getAllInvoices(): array
    {
        return $this->entityManager->getRepository(Invoice::class)->findAll();
    }

    public function getInvoicesByCustomer(Customer $customer): array
    {
        return $this->entityManager->getRepository(Invoice::class)->findBy(['customer' => $customer]);
    }

    public function deleteInvoice(Invoice $invoice): void
    {
        $this->entityManager->remove($invoice);
        $this->entityManager->flush();
    }
}

