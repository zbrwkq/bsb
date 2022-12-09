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

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_register', [], Response::HTTP_SEE_OTHER);
    }
}
