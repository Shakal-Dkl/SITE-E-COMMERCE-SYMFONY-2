<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Entity\OrderItem;
use App\Service\CartService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getDetailedItems(),
            'total' => $cartService->getTotal(),
        ]);
    }

    #[Route('/cart/remove/{lineKey}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(string $lineKey, Request $request, CartService $cartService): RedirectResponse
    {
        if ($this->isCsrfTokenValid('remove_'.$lineKey, (string) $request->request->get('_token'))) {
            $cartService->remove($lineKey);
            $this->addFlash('success', 'Article retiré du panier.');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(
        Request $request,
        CartService $cartService,
        StripeService $stripeService,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $items = $cartService->getDetailedItems();

        if ($items === []) {
            $this->addFlash('error', 'Le panier est vide.');

            return $this->redirectToRoute('app_cart');
        }

        $order = new CustomerOrder();
        $order->setUser($this->getUser());
        $order->setTotal($cartService->getTotal());

        foreach ($items as $item) {
            $orderItem = new OrderItem();
            $orderItem->setProduct($item['product']);
            $orderItem->setProductName((string) $item['product']->getName());
            $orderItem->setUnitPrice((float) $item['product']->getPrice());
            $orderItem->setQuantity((int) $item['quantity']);
            $orderItem->setSize((string) $item['size']);
            $order->addItem($orderItem);
        }

        $entityManager->persist($order);
        $entityManager->flush();

        $successUrl = $this->generateUrl('app_cart_success', ['orderId' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('app_cart', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $checkout = $stripeService->startCheckout(
            $items,
            $successUrl,
            $cancelUrl,
        );

        $order->setStripeSessionId($checkout['sessionId']);
        $entityManager->flush();

        return $this->redirect($checkout['checkoutUrl']);
    }

    #[Route('/cart/success/{orderId}', name: 'app_cart_success')]
    public function success(int $orderId, EntityManagerInterface $entityManager, CartService $cartService): RedirectResponse
    {
        $order = $entityManager->getRepository(CustomerOrder::class)->find($orderId);

        if ($order) {
            $order->setStatus('paid');
            $entityManager->flush();
        }

        $cartService->clear();
        $this->addFlash('success', 'Commande finalisée avec succès.');

        return $this->redirectToRoute('app_cart');
    }
}
