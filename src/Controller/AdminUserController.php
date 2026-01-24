<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_user_admin_index')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['createAt' => 'DESC']);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}/toggle', name: 'toggle', methods: ['POST'])]
    public function toggle(User $user, EntityManagerInterface $em): RedirectResponse
    {
        // empêcher de se désactiver soi-même en admin (optionnel)
        if ($this->getUser() === $user) {
            $this->addFlash('danger', 'Tu ne peux pas désactiver ton propre compte.');
            return $this->redirectToRoute('app_admin_user_index');
        }

        $user->setIsActive(!$user->isActive());
        $em->flush();

        $message = $user->isActive()
            ? 'Utilisateur activé avec succès.'
            : 'Utilisateur désactivé avec succès.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_admin_user_index');
    }
}
