<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\PanierRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}/compte')]
class UserController extends AbstractController
{
    /**
     * Retourne le liste des commande de l'utilisateur
     */
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {
        $rawCommandes = $panierRepository->findByUserAndEtat($this->getUser()->getId(), true);
        $commandes = [];
        foreach ($rawCommandes as $commande) {
            $commandes[$commande->getId()] = [
                'commande' => $commande,
                'montant' => 0
            ];

            foreach ($commande->getContenuPaniers() as $cp) {
                $commandes[$commande->getId()]['montant'] += $cp->getProduit()->getPrix() * $cp->getQuantite();
            }
        }
        
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser(),
            'commandes' => $commandes,
        ]);
    }

    /**
     * Permet de modifier les infos de son compte
     */
    #[Route('/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            $this->addFlash('success', $translator->trans('Informations mise à jour'));
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Retourne la liste des derniers inscrits du jour
     */
    #[Route('/inscrits', name: 'app_user_inscrits')]
    public function derniersInscrits(UserRepository $ur): Response
    {
        $date = date('Y-m-d');
        return $this->render('user/last_user.html.twig', [
            'users' => $ur->registerToday($date)
        ]);
    }
}
