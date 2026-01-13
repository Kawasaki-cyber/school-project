<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager
    ): Response {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_admin');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var string $plainPassword */
                $plainPassword = $form->get('plainPassword')->getData();

                if (empty($plainPassword)) {
                    $this->addFlash('error', 'Password cannot be empty.');
                    return $this->render('registration/index.html.twig', [
                        'registrationForm' => $form,
                    ]);
                }

                // Check if email already exists
                $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
                if ($existingUser) {
                    $this->addFlash('error', 'This email is already registered. Please use a different email or log in.');
                    return $this->render('registration/index.html.twig', [
                        'registrationForm' => $form,
                    ]);
                }

                // Encode the plain password
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                
                // Set default role as PATIENT (as per requirements: ADMIN, MEDECIN, PATIENT)
                $user->setRoles(['ROLE_PATIENT']);

                try {
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Registration successful! You can now log in.');
                    return $this->redirectToRoute('app_login');
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $this->addFlash('error', 'This email is already registered. Please use a different email or log in.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Registration failed: ' . $e->getMessage());
                }
            } else {
                // Form submitted but invalid - show specific errors
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                if (!empty($errors)) {
                    $this->addFlash('error', 'Please correct the following errors: ' . implode(', ', $errors));
                }
            }
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
