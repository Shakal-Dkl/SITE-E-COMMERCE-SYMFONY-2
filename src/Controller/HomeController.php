<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Contrôleur de la page d'accueil.
// Son rôle est d'afficher uniquement les produits marqués comme "mis en avant".
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        // On récupère les produits "featured" pour alimenter la vitrine d'accueil.
        $featuredProducts = $productRepository->findBy(['featured' => true], ['id' => 'ASC']);

        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
        ]);
    }
}
