<?php

namespace App\Service;

use App\Entity\Product;
use App\Exception\ProductOutOfStockException;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function addToCart(Product $product, int $quantity): void
    {
        if ($product->getQuantity() < $quantity) {
            throw new ProductOutOfStockException('Not enough stock available');
        }

        $cart = $this->session->get('cart', []);
        $productId = $product->getId();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'quantity' => $quantity,
                'price' => $product->getPrice(),
                'name' => $product->getName()
            ];
        }

        $this->session->set('cart', $cart);
    }

    public function removeFromCart(int $productId): void
    {
        $cart = $this->session->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->session->set('cart', $cart);
        }
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        $cart = $this->session->get('cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            $this->session->set('cart', $cart);
        }
    }

    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    public function clearCart(): void
    {
        $this->session->remove('cart');
    }
}
