<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET', 'POST'])]
    /**
     * Permet l'affichage et, si nous sommes admin, de récupérer le formulaire d'ajout de produit
     */
    public function index(Request $request, ProduitRepository $produitRepository, TranslatorInterface $translator): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();

            if($photoFile){
                $newPhotoName = uniqid().'.'.$photoFile->guessExtension();

                try{
                    $photoFile->move(
                        $this->getParameter('upload_directory'),
                        $newPhotoName
                    );
                }catch(FileException $e){
                    $this->addFlash('danger', $translator->trans('flash.produit.erreur.upload_image'));
                    return $this->redirectToRoute('app_produit_new');
                }

                // Enregistrement en BDD
                $produit->setPhoto($newPhotoName);
            }

            $produitRepository->save($produit, true);
            $this->addFlash('success', $translator->trans('flash.produit.ajoute'));

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Retourne les détails d'un produit
     */
    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    /**
     * Retourne le formulaire d'édition d'un produit
     */
    #[Route('/edit/{id}', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);

            $this->addFlash('success', $translator->trans('flash.produit.modifie'));
            return $this->redirectToRoute('app_produit_show', ['id' => $produit->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet de supprimer un produit
     */
    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository, TranslatorInterface $translator): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
        }

        $this->addFlash('success', $translator->trans('flash.produit.supprime'));
        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
