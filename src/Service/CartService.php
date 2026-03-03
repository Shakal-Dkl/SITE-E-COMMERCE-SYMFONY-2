<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_KEY = 'cart';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function add(int $productId, string $size): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_KEY, []);
        $key = $this->buildKey($productId, $size);

        if (!isset($cart[$key])) {
            $cart[$key] = [
                'productId' => $productId,
                'size' => $size,
                'quantity' => 0,
            ];
        }

        $cart[$key]['quantity']++;

        $session->set(self::CART_KEY, $cart);
    }

    public function remove(string $lineKey): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_KEY, []);

        unset($cart[$lineKey]);

        $session->set(self::CART_KEY, $cart);
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->set(self::CART_KEY, []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getDetailedItems(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_KEY, []);
        $details = [];

        foreach ($cart as $lineKey => $line) {
            $product = $this->productRepository->find($line['productId']);

            if (!$product) {
                continue;
            }

            $details[$lineKey] = [
                'product' => $product,
                'size' => $line['size'],
                'quantity' => $line['quantity'],
                'lineTotal' => $product->getPrice() * $line['quantity'],
            ];
        }

        return $details;
    }

    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getDetailedItems() as $item) {
            $total += $item['lineTotal'];
        }

        return $total;
    }

    private function buildKey(int $productId, string $size): string
    {
        return $productId.'-'.$size;
    }
}
