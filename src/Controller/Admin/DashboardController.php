<?php

namespace App\Controller\Admin;

use DateTime;
use App\Entity\Produit;
use App\Form\ProduitFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route("/admin")]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', []);
    }

    #[Route('/voir-les-produits', name: 'show_produits', methods: ['GET'])]
    public function showProduits(EntityManagerInterface $entityManager): Response
    {
        // Récupération en BDD de toutes les entités Produits, grâce au repository
        $produits = $entityManager->getRepository(Produit::class)->findBy(['deletedAt' => null]);
        $produitsArchives = $entityManager->getRepository(Produit::class)->findAllArchived();

        return $this->render('admin/show_produits.html.twig', [
            'produits' => $produits,
            'archiver' => $produitsArchives
        ]);
    }

    #[Route('/Ajouter-un-produit', name: 'create_produit', methods: ['GET', 'POST'])]
    public function createProduit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit();

        $form = $this->createForm(ProduitFormType::class, $produit)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produit->setCreatedAt(new DateTime());
            $produit->setUpdatedAt(new DateTime());

            /** @var UploadedFile $image */
            $image = $form->get('image')->getData();

            if ($image) {
                $this->handleFile($image, $slugger, $produit);
            }

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('admin/add_produit.html.twig', [
            'form_produit' => $form->createView(),
        ]);
    }

    #[Route('/modifier-le-produit/{id}', name: 'update_produit', methods: ['GET', 'POST'])]
    public function updateProduit(Produit $produit, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProduitFormType::class, $produit)->handleRequest($request);

        $originalImage = $produit->getImage();
        $imageModifiee = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $produit->setUpdatedAt(new DateTime());
            $photo = $form->get('image')->getData();

            if ($photo) {
                // Méthode créée par nous-mêmes pour réutiliser
                $this->handleFile($photo, $slugger, $produit);
            } else {
                $produit->setImage($originalImage);
            } // end if photo

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès');
            return $this->redirectToRoute('app_dashboard');
        } // end form

        return $this->render('admin/add_produit.html.twig', [
            'form_produit' => $form->createView(),
            'produit' => $produit
        ]);
    }

    #[Route('/archiver-un-produit/{id}', name: 'archiver_un_produit', methods: ['GET'])]
    public function softDeleteProduit(Produit $produit, EntityManagerInterface $entityManager): RedirectResponse
    {
        $produit->setDeletedAt(new DateTime());

        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Produit a bien été archivé');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/restaurer-un-produit/{id}', name: 'restaurer_un_produit', methods: ['GET'])]
    public function restoreProduit(Produit $produit, EntityManagerInterface $entityManager): RedirectResponse
    {
        $produit->setDeletedAt(null); // Réinitialiser la date de suppression

        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Produit restauré avec succès');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/supprimer-un-produit/{id}', name: 'supprimer_un_produit', methods: ['GET'])]
    public function hardDeleteProduit(Produit $produit, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Récupérer le nom du fichier image
        $imageFilename = $produit->getImage();
    
        // Supprimer l'entité du produit de la base de données
        $entityManager->remove($produit);
        $entityManager->flush();
    
        // Supprimer physiquement le fichier image du serveur
        $filesystem = new Filesystem();
        $uploadsDir = $this->getParameter('uploads_dir'); // Le répertoire où sont stockées les images
        $imagePath = $uploadsDir . '/' . $imageFilename;
    
        if ($filesystem->exists($imagePath)) {
            $filesystem->remove($imagePath);
        }
    
        $this->addFlash('success', 'Produit supprimé définitivement');
        return $this->redirectToRoute('app_dashboard');
    }

    private function handleFile(UploadedFile $image, SluggerInterface $slugger, Produit $produit): void
    {
        $extension = '.' . $image->guessExtension();
        $safeFilename = $slugger->slug($produit->getTitle());

        $newFilename = $safeFilename . '_' . uniqid() . $extension;

        try {
            $image->move($this->getParameter('uploads_dir'), $newFilename);
            $produit->setImage($newFilename);
        } catch (FileException $exception) {
            // code à exécuter en cas d'erreur
        }
    }
}

