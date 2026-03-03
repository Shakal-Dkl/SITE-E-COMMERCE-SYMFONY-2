<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Service\StripeService;
use PHPUnit\Framework\TestCase;

class StripeServiceTest extends TestCase
{
    public function testSimulationModeReturnsSuccessUrl(): void
    {
        $product = new Product();
        $product->setName('Pokeball');
        $product->setPrice(45.00);

        $items = [
            'line-1' => [
                'product' => $product,
                'size' => 'M',
                'quantity' => 1,
                'lineTotal' => 45.00,
            ],
        ];

        $service = new StripeService('sk_test_***');
        $result = $service->startCheckout($items, 'http://localhost/cart/success', 'http://localhost/cart');

        self::assertTrue($result['simulated']);
        self::assertNull($result['sessionId']);
        self::assertStringContainsString('/cart/success', $result['checkoutUrl']);
    }
}
