<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $newProduct = new Product();
        $newProductForm = $this->createForm(ProductType::class, $newProduct);
        $newProductForm->handleRequest($request);

        if ($newProductForm->isSubmitted() && $newProductForm->isValid()) {
            $entityManager->persist($newProduct);
            $entityManager->flush();
            $this->addFlash('success', 'Produit ajouté.');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/index.html.twig', [
            'products' => $productRepository->findBy([], ['id' => 'ASC']),
            'newProductForm' => $newProductForm,
        ]);
    }

    #[Route('/admin/product/{id}/edit', name: 'app_admin_product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Produit modifié.');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }

    #[Route('/admin/product/{id}/delete', name: 'app_admin_product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_product_'.$product->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('app_admin');
    }
}
