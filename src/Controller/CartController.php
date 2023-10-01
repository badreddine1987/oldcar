<?php

namespace App\Controller;

use App\Entity\Produit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/panier')]
class CartController extends AbstractController
{
    #[Route('/voir-mon-panier', name: 'show_panier', methods: ['GET'])]
    public function showPanier(SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        $total = 0;

        foreach ($panier as $item) {
            $totalItem = $item['produit']->getPrice() * $item['quantity'];
            $total += $totalItem;
        }

        return $this->render('panier/panier.html.twig', [
            'total' => $total,
            'panier' => $panier
        ]);
    }

    #[Route('/ajouter-un-produit/{id}', name: 'add_item', methods: ['GET'])]
    public function addItem(Produit $produit, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        if (!empty($panier[$produit->getId()])) {
            ++$panier[$produit->getId()]['quantity'];
        } else {
            $panier[$produit->getId()]['quantity'] = 1;
            $panier[$produit->getId()]['produit'] = $produit;
        }

        $session->set('panier', $panier);

        $this->addFlash('success', 'Le Produit a bien été ajouté à votre panier');
        return $this->redirectToRoute('show_panier');
    }

    #[Route('/retirer-quantite/{id}', name: 'retirer_quantite', methods: ['GET'])]
    public function retirerQuantite(Produit $produit, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        if (!empty($panier[$produit->getId()]) && $panier[$produit->getId()]['quantity'] > 0) {
            --$panier[$produit->getId()]['quantity'];
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('show_panier');
    }

    #[Route('/supprimer-produit/{id}', name: 'supprimer_produit', methods: ['GET'])]
    public function supprimerProduit(Produit $produit, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        if (!empty($panier[$produit->getId()])) {
            unset($panier[$produit->getId()]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('show_panier');
    }

    #[Route('/vider-panier', name: 'vider_panier', methods: ['GET'])]
    public function viderPanier(SessionInterface $session): Response
    {
        $session->set('panier', []);

        $this->addFlash('success', 'Le panier a été vidé avec succès.');

        return $this->redirectToRoute('show_panier');
    }
}
