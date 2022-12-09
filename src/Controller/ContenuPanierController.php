<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContenuPanierController extends AbstractController
{
    #[Route('/contenu/panier/{id}', name: 'app_contenu_panier')]
    public function index(ContenuPanierRepository $cpr, Produit $produit = null, PanierRepository $pr, UserRepository $user): Response
    {
        $contenuPanier = new ContenuPanier();
        $panier = null;

        // Verifie si un panier impayé est déjà présent et le récupère
        foreach($this->getUser()->getPanier() as $p){
            if(!$p->isEtat()){
                $panier = $p;
            }
        }
        
        // créé un nouveau panier sinon
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

                $this->addFlash('success', 'Produit ajouté au panier');
                return $this->redirectToRoute('app_produit_index');
            // sinon redirige l'utilisateur
            }else{
                $this->addFlash('danger', 'Le produit n\'existe pas');
                return $this->redirectToRoute('app_produit_index');
            }
        }else{
            foreach($panier->getContenuPaniers() as $cp){
                if($cp->getProduit()->getId() == $produit->getId()){
                    $cp->setQuantite($cp->getQuantite() + 1);
                    $cpr->save($cp, true);
                    $this->addFlash('success', 'Produit ajouté au panier');
                    return $this->redirectToRoute('app_produit_index');
                }
            }

            $contenuPanier->setQuantite(1);
            $contenuPanier->setProduit($produit);
            $contenuPanier->setPanier($panier);
            $cpr->save($contenuPanier, true);
        
            $this->addFlash('success', 'Produit ajouté au panier');
            return $this->redirectToRoute('app_produit_index');
        }

    }
}
