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

// Contrôleur panier / commande.
// Il orchestre le parcours d'achat: panier -> création commande -> paiement.
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
        // Protection CSRF sur la suppression d'une ligne du panier.
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

        // Garde-fou: impossible de lancer un checkout avec un panier vide.
        if ($items === []) {
            $this->addFlash('error', 'Le panier est vide.');

            return $this->redirectToRoute('app_cart');
        }

        $order = new CustomerOrder();
        // L'utilisateur connecté devient le propriétaire de la commande.
        $order->setUser($this->getUser());
        $order->setTotal($cartService->getTotal());

        // On fige les lignes de commande (nom, prix unitaire, taille, quantité).
        // Cela évite que l'historique change si le produit est modifié plus tard.
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

        // URLs absolues requises par Stripe pour retour succès/annulation.
        $successUrl = $this->generateUrl('app_cart_success', ['orderId' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('app_cart', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $checkout = $stripeService->startCheckout(
            $items,
            $successUrl,
            $cancelUrl,
        );

        $order->setStripeSessionId($checkout['sessionId']);
        $entityManager->flush();

        // Redirection vers Stripe (ou vers la simulation en environnement local).
        return $this->redirect($checkout['checkoutUrl']);
    }

    #[Route('/cart/success/{orderId}', name: 'app_cart_success')]
    public function success(int $orderId, EntityManagerInterface $entityManager, CartService $cartService): RedirectResponse
    {
        $order = $entityManager->getRepository(CustomerOrder::class)->find($orderId);

        // En mode démo, on marque la commande payée au retour "success".
        if ($order) {
            $order->setStatus('paid');
            $entityManager->flush();
        }

        // Une fois la commande finalisée, on vide le panier session.
        $cartService->clear();
        $this->addFlash('success', 'Commande finalisée avec succès.');

        return $this->redirectToRoute('app_cart');
    }
}
