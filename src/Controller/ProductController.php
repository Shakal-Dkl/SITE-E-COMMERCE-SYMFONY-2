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

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $range = $request->query->get('range');
        $products = $productRepository->findByPriceRange(is_string($range) ? $range : null);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'selectedRange' => $range,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', requirements: ['id' => '\\d+'])]
    public function show(Product $product, Request $request, CartService $cartService): Response
    {
        $form = $this->createForm(AddToCartType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $size = (string) $form->get('size')->getData();
            $cartService->add((int) $product->getId(), $size);
            $this->addFlash('success', 'Produit ajouté au panier.');

            return $this->redirectToRoute('app_cart');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'addToCartForm' => $form,
        ]);
    }
}
