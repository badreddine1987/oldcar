<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Produit;

#[Route('/paiement')]
class PaymentController extends AbstractController
{
    #[Route('/formulaire', name: 'formulaire_paiement', methods: ['GET', 'POST'])]
    public function formulairePaiement(Request $request, SessionInterface $session): Response
    {
        // Récupérer le panier depuis la session
        $panier = $session->get('panier', []);

        // Votre logique de traitement du formulaire de paiement ici

        // Par exemple, si le formulaire est soumis
        if ($request->isMethod('POST')) {
            // Traitement du paiement ici
            // Vous pouvez utiliser un service de paiement ou effectuer d'autres opérations nécessaires

            // Après le paiement réussi, vous pouvez vider le panier
            $session->set('panier', []);
            
            $this->addFlash('success', 'Paiement effectué avec succès ! Le panier a été vidé.');
            
            return $this->redirectToRoute('show_panier');
        }

        return $this->render('panier/formulaire_paiement.html.twig', [
            'panier' => $panier,
        ]);
    }
}
