<?php

namespace App\Controller;

use App\Repository\ContenuPanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Panier;
use App\Repository\PanierRepository;
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
            'panier' => $this->getUser()->getActivePanier(),
        ]);
    }

    // Passe l'etat du panier actif de l'utilisateur a 1 et met à jour la date d'achat
    #[Route('/panier/achat', name: 'app_panier_achat')]
    public function achatPanier(EntityManagerInterface $em): Response
    {
        $p = $this->getUser()->getActivePanier();
        if($p != null){
            date_default_timezone_set('Europe/Paris');
            $p->setDateAchat(\DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));
            $p->setEtat(1);
    
            foreach($p->getContenuPaniers() as $cp){
                $produit = $cp->getProduit();
                $produit->setStock($produit->getStock() - $cp->getQuantite());
                $em->persist($produit);
            }
    
            $em->persist($p);
            $em->flush();
    
            $this->addFlash('success', 'Votre commande est validée');
            return $this->redirectToRoute('app_commande', ['id' => $p->getId()]);
        }

        $this->addFlash('danger', 'Une erreur est survenue');
        return $this->redirectToRoute('app_produit_index');
    }

    #[Route('/commande/{id}', name: 'app_commande')]
    public function commande(Panier $panier): Response
    {
        return $this->render('panier/commande.html.twig', [
            'commande' => $panier,
        ]);
    }

    #[Route('/non-achetes', name: 'app_non_achetes')]
    public function paniersNonAchetes(PanierRepository $panier): Response
    {
        // dump($panier->findAll());
        // die;
        return $this->render('panier/non_achetes.html.twig', [
            'panierNonAchetes' => $panier->findAll(),
        ]);
    }
}
