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

// Contrôleur back-office réservé aux administrateurs.
// Il gère les opérations CRUD sur les produits.
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        // Formulaire de création d'un nouveau produit.
        $newProduct = new Product();
        $newProductForm = $this->createForm(ProductType::class, $newProduct);
        $newProductForm->handleRequest($request);

        if ($newProductForm->isSubmitted() && $newProductForm->isValid()) {
            // Persist = insertion en base d'un nouveau produit.
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
        // Ici, l'objet Product est injecté automatiquement via l'ID de l'URL.
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pas de persist() ici: l'entité existe déjà, on flush simplement les modifications.
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
        // Vérification CSRF pour éviter une suppression non voulue via lien externe.
        if ($this->isCsrfTokenValid('delete_product_'.$product->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit supprimé.');
        }

        return $this->redirectToRoute('app_admin');
    }
}
