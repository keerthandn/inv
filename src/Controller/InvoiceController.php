<?php
namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Product;
use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    /**
     * @Route("/invoice/new", name="invoice_new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoice();

        // Assuming 'customer_id' and 'products' are passed as parameters
        $customerId = $request->get('customer_id');
        $customer = $entityManager->getRepository(Customer::class)->find($customerId);

        // Validate customer
        if (null === $customer) {
            throw $this->createNotFoundException('Customer not found for ID: ' . $customerId);
        }

        $productsData = $request->get('products'); // Should contain product IDs and quantities

        // Validate products data
        if (empty($productsData)) {
            throw $this->createNotFoundException('Products data is missing.');
        }

        // Prepare the invoice data
        $products = [];
        foreach ($productsData as $productData) {
            $product = $entityManager->getRepository(Product::class)->find($productData['id']);
            if ($product) {
                $products[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'quantity' => $productData['quantity'],
                ];
            } else {
                throw $this->createNotFoundException('Product not found for ID: ' . $productData['id']);
            }
        }

        // Set invoice properties
        $invoice->setCustomer($customer);
        $invoice->setProducts($products);
        $invoice->setTotal($this->calculateTotal($products));

        $entityManager->persist($invoice);
        $entityManager->flush();

        return $this->redirectToRoute('invoice_list');
    }

    private function calculateTotal(array $products): float
    {
        $total = 0.0;

        foreach ($products as $product) {
            $total += $product['price'] * $product['quantity'];
        }

        return $total;
    }

    /**
     * @Route("/invoice", name="invoice_list")
     */
    public function list(EntityManagerInterface $entityManager): Response
    {
        $invoices = $entityManager->getRepository(Invoice::class)->findAll();

        return $this->render('invoice/list.html.twig', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * @Route("/invoice/edit/{id}", name="invoice_edit")
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, Invoice $invoice): Response
    {
        if ($request->isMethod('POST')) {
            $customerId = $request->get('customer_id');
            $customer = $entityManager->getRepository(Customer::class)->find($customerId);
            
            // Validate customer
            if (null === $customer) {
                throw $this->createNotFoundException('Customer not found for ID: ' . $customerId);
            }

            $productsData = $request->get('products');

            // Validate products data
            if (empty($productsData)) {
                throw $this->createNotFoundException('Products data is missing.');
            }

            // Prepare the products array
            $products = [];
            foreach ($productsData as $productData) {
                $product = $entityManager->getRepository(Product::class)->find($productData['id']);
                if ($product) {
                    $products[] = [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'price' => $product->getPrice(),
                        'quantity' => $productData['quantity'],
                    ];
                } else {
                    throw $this->createNotFoundException('Product not found for ID: ' . $productData['id']);
                }
            }

            // Update invoice properties
            $invoice->setCustomer($customer);
            $invoice->setProducts($products);
            $invoice->setTotal($this->calculateTotal($products));

            $entityManager->flush();

            return $this->redirectToRoute('invoice_list');
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * @Route("/invoice/delete/{id}", name="invoice_delete", methods={"DELETE"})
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Invoice $invoice): Response
    {
        if ($this->isCsrfTokenValid('delete' . $invoice->getId(), $request->request->get('_token'))) {
            $entityManager->remove($invoice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('invoice_list');
    }
}
