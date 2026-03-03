<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

// Service métier du panier.
// Toute la logique panier est centralisée ici pour garder les contrôleurs simples.
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
        // Panier stocké en session: persiste tant que la session utilisateur est active.
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_KEY, []);
        $key = $this->buildKey($productId, $size);

        // Chaque combinaison produit+t﻿aille a sa propre ligne.
        if (!isset($cart[$key])) {
            $cart[$key] = [
                'productId' => $productId,
                'size' => $size,
                'quantity' => 0,
            ];
        }

        $cart[$key]['quantity']++;

        // Écriture finale en session.
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

        // On enrichit la session brute avec les infos produit venant de la base.
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
        // Total calculé dynamiquement à partir des lignes détaillées.
        $total = 0;

        foreach ($this->getDetailedItems() as $item) {
            $total += $item['lineTotal'];
        }

        return $total;
    }

    private function buildKey(int $productId, string $size): string
    {
        // Clé stable de ligne panier, ex: "12-M".
        return $productId.'-'.$size;
    }
}
