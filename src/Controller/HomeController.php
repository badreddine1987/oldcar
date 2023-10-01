<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {

         // Récupération en BDD de toutes les entités Produits, grâce au repository
         $produits = $entityManager->getRepository(Produit::class)->findBy(['deletedAt' => null], ['createdAt' => 'DESC']);

        return $this->render('home/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/produit', name: 'app_product')]
    public function produit(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        // Récupération en BDD de toutes les entités Produits, grâce au repository
        $produits = $entityManager->getRepository(Produit::class)->findBy(['deletedAt' => null], ['createdAt' => 'DESC']);

        // Définir la direction de tri par défaut (ascendant par exemple)
        // $direction = $request->query->get('direction', 'asc');

        // Triez les produits en fonction de votre besoin
        // Par exemple, triez par nom de produit
        // Assurez-vous d'ajuster le champ de tri en conséquence
        // Vous pouvez également ajuster la direction du tri en fonction des besoins
        // usort($produits, function ($a, $b) use ($direction) {
        //     if ($a->getTitle() == $b->getTitle()) {
        //         return 0;
        //     }
        //     return ($a->getTitle() < $b->getTitle()) ? -1 : 1;
        // });

        // Utilisation de KnpPaginator pour paginer les produits triés
        $data = $paginator->paginate(
            $produits,
            $request->query->getInt('page', 1),
            $this->getParameter('knp_paginator.page_limit')
        );

        return $this->render('home/produit.html.twig', [
            'produits' => $data,
        ]);
    }

        #[Route('/produit/{id}', name: 'app_product_detail')]
        public function produitDetail(Produit $produit, EntityManagerInterface $entityManager): Response
        {
            $produits = $entityManager->getRepository(Produit::class)->findBy(['deletedAt' => null], ['createdAt' => 'DESC']);


        return $this->render('home/produit_detail.html.twig', [
            'produit' => $produit,
            'produits' => $produits
        ]);
    }
}
