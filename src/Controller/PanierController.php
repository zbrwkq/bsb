<?php

namespace App\Controller;

use App\Repository\ContenuPanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{_locale}')]
class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(ContenuPanierRepository $cpr): Response
    {
        return $this->render('panier/index.html.twig', [
            'articles' => $cpr->findAll(),
        ]);
    }

    #[Route('/panier/achat', name: 'app_panier_achat')]
    public function achatPanier(EntityManagerInterface $em): Response
    {
        foreach($this->getUser()->getPanier() as $p){
            if(!$p->isEtat()){
                $dateAchat = date('Y-m-d H:i:s');
                $p->setDateAchat(\DateTime::createFromFormat('Y-m-d H:i:s', $dateAchat));
                $p->setEtat(1);

                foreach($p->getContenuPaniers() as $cp){
                    $produit = $cp->getProduit();
                    $produit->setStock($produit->getStock() - $cp->getQuantite());
                    $em->persist($produit);
                }
                die;
                $em->persist($p);
                $em->flush();

                $this->addFlash('success', 'Votre commande est validée');
                return $this->redirectToRoute('app_produit_index');
            }
        }

        $this->addFlash('danger', 'Problème');
        return $this->redirectToRoute('app_produit_index');
    }
    
    #[Route('/commande/{id}', name: 'app_commande')]
    public function commande(Panier $panier): Response
    {
        return $this->render('panier/commande.html.twig', [
            'commande' => $panier,
        ]);
    }
}
