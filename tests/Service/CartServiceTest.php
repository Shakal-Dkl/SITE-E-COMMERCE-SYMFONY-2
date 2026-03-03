<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CartService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CartServiceTest extends TestCase
{
    public function testAddAndTotal(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack->push($request);

        $product = new Product();
        $product->setName('Blackbelt');
        $product->setPrice(29.90);

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository
            ->method('find')
            ->willReturnMap([
                [1, $product],
            ]);

        $cartService = new CartService($requestStack, $productRepository);
        $cartService->add(1, 'M');
        $cartService->add(1, 'M');

        $items = $cartService->getDetailedItems();

        self::assertCount(1, $items);
        self::assertArrayHasKey('1-M', $items);
        self::assertSame(2, $items['1-M']['quantity']);
        self::assertSame(59.8, $cartService->getTotal());
    }

    public function testRemoveLine(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack->push($request);

        $product = new Product();
        $product->setName('Street');
        $product->setPrice(34.50);

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository
            ->method('find')
            ->willReturn($product);

        $cartService = new CartService($requestStack, $productRepository);
        $cartService->add(1, 'L');
        $cartService->remove('1-L');

        self::assertCount(0, $cartService->getDetailedItems());
        self::assertSame(0.0, $cartService->getTotal());
    }
}
