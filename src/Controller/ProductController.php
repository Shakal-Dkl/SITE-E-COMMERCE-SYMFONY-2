<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\AddToCartType;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Contrôleur catalogue + fiche produit.
// Il gère l'affichage des produits et l'ajout au panier depuis la fiche détail.
class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        // Le filtre de prix est passé en query string (?range=...).
        $rangeParam = $request->query->get('range');
        $range = null;

        if (is_string($rangeParam)) {
            $range = $rangeParam;
        }

        $products = $productRepository->findByPriceRange($range);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'selectedRange' => $range,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', requirements: ['id' => '\\d+'])]
    public function show(Product $product, Request $request, CartService $cartService): Response
    {
        // Formulaire minimal: l'utilisateur choisit uniquement la taille.
        $form = $this->createForm(AddToCartType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On délègue la logique panier au service dédié (pas au contrôleur).
            $size = (string) $form->get('size')->getData();
            $productId = (int) $product->getId();

            $cartService->add($productId, $size);
            $this->addFlash('success', 'Produit ajouté au panier.');

            return $this->redirectToRoute('app_cart');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'addToCartForm' => $form,
        ]);
    }
}
