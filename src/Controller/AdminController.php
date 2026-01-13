<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Entity\User;
use App\Entity\RendezVous;
use App\Repository\MedecinRepository;
use App\Repository\UserRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function dashboard(
        UserRepository $userRepository,
        MedecinRepository $medecinRepository,
        RendezVousRepository $rendezVousRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/dashboard.html.twig', [
            'users_count' => count($userRepository->findAll()),
            'medecins_count' => count($medecinRepository->findAll()),
            'rendezvous_count' => count($rendezVousRepository->findAll()),
        ]);
    }

    // Users Management
    #[Route('/users', name: 'app_admin_users')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findAll();
        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}', name: 'app_admin_user_show')]
    public function showUser(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_users');
    }

    // Rendezvous Management
    #[Route('/rendezvous', name: 'app_admin_rendezvous')]
    public function listRendezvous(RendezVousRepository $rendezVousRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $rendezvous = $rendezVousRepository->findAll();
        return $this->render('admin/rendezvous/index.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

    #[Route('/rendezvous/{id}', name: 'app_admin_rendezvous_show')]
    public function showRendezvous(RendezVous $rendezvous): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/rendezvous/show.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

    // Medecin CRUD
    #[Route('/medecins', name: 'app_admin_medecins')]
    public function listMedecins(MedecinRepository $medecinRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $medecins = $medecinRepository->findAll();
        return $this->render('admin/medecins/index.html.twig', [
            'medecins' => $medecins,
        ]);
    }

    #[Route('/medecins/{id}', name: 'app_admin_medecin_show')]
    public function showMedecin(Medecin $medecin): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/medecins/show.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/medecins/new', name: 'app_admin_medecin_new')]
    public function newMedecin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $medecin = new Medecin();
        $form = $this->createForm(\App\Form\MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medecin);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_medecins');
        }

        return $this->render('admin/medecins/form.html.twig', [
            'form' => $form,
            'title' => 'Create Medecin',
        ]);
    }

    #[Route('/medecins/{id}/edit', name: 'app_admin_medecin_edit')]
    public function editMedecin(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(\App\Form\MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_medecins');
        }

        return $this->render('admin/medecins/form.html.twig', [
            'form' => $form,
            'title' => 'Edit Medecin',
            'medecin' => $medecin,
        ]);
    }

    #[Route('/medecins/{id}/delete', name: 'app_admin_medecin_delete', methods: ['POST'])]
    public function deleteMedecin(Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($medecin);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_medecins');
    }
}
