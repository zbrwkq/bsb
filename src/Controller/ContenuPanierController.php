<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContenuPanierController extends AbstractController
{
    #[Route('/contenu/panier/{id}', name: 'app_contenu_panier')]
    public function index(ContenuPanierRepository $cpr, Produit $produit = null, PanierRepository $pr, TranslatorInterface $translator): Response
    {
        //  si le produit n'est plus en stock
        if($produit->getStock() <= 0 ){
            $this->addFlash('danger', $translator->trans('produit.plus_stock'));
            return $this->redirectToRoute('app_produit_show', ['id' => $produit->getId()]);
        }

        
        $contenuPanier = new ContenuPanier();

        // recupère la panier impayé, null si aucun panier n'est impayé
        $panier = $this->getUser()->getActivePanier();
        
        // Si aucun panier créé un nouveau panier et insère dans la bdd
        if($panier == null){
            $panier = new Panier();

            // si le produit existe, l'ajoute au panier
            if($produit != null){
                $panier->setUser($this->getUser());
                $contenuPanier->setQuantite(1);
                $contenuPanier->setProduit($produit);
                $contenuPanier->setPanier($panier);

                $pr->save($panier, true);
                $cpr->save($contenuPanier, true);

                $this->addFlash('success',  $translator->trans('flash.panier.ajoute'));
                return $this->redirectToRoute('app_panier');
            // sinon redirige l'utilisateur
            }else{
                $this->addFlash('danger', $translator->trans('produit.existe_pas'));
                return $this->redirectToRoute('app_produit_index');
            }
        }else{
            // verfie que le produit n'est pas déjà présent dans le panier et incrémente la quantité si c'est le cas
            foreach($panier->getContenuPaniers() as $cp){
                if($cp->getProduit()->getId() == $produit->getId()){
                    $cp->setQuantite($cp->getQuantite() + 1);
                    $cpr->save($cp, true);
                    $this->addFlash('success',  $translator->trans('flash.panier.ajoute'));
                    return $this->redirectToRoute('app_panier');
                }
            }

            // ajoute le produit sinon
            $contenuPanier->setQuantite(1);
            $contenuPanier->setProduit($produit);
            $contenuPanier->setPanier($panier);
            $cpr->save($contenuPanier, true);
        
            $this->addFlash('success',  $translator->trans('flash.panier.ajoute'));
            return $this->redirectToRoute('app_panier');
        }
    }
    
    #[Route('/contenu/panier/delete/{id}', name: 'app_contenu_panier_delete')]
    public function delete(ContenuPanier $cp, ContenuPanierRepository $contenuPanierRepository, TranslatorInterface $translator): Response
    {
        $contenuPanierRepository->remove($cp, true);

        $this->addFlash('success', $translator->trans('flash.panier.supprime'));
        return $this->redirectToRoute('app_panier', [], Response::HTTP_SEE_OTHER);
    }
}
