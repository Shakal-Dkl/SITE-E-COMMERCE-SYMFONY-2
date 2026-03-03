<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

// Contrôleur d'inscription client + validation de l'email.
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmailVerifier $emailVerifier,
    ): Response {
        // Un utilisateur déjà connecté n'a pas besoin de repasser par l'inscription.
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le mot de passe est toujours hashé avant stockage en base.
            $user->setPassword($userPasswordHasher->hashPassword(
                $user,
                (string) $form->get('plainPassword')->getData()
            ));

            // Dans le contexte de ce projet, un inscrit devient client par défaut.
            $user->setRoles(['ROLE_CLIENT']);

            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi de l'email de confirmation avec lien signé.
            $emailVerifier->sendEmailConfirmation('app_verify_email', $user, (new TemplatedEmail())
                ->from(new Address('noreply@stubborn.local', 'Stubborn'))
                ->to((string) $user->getEmail())
                ->subject('Confirme ton inscription')
                ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Inscription réussie. Vérifie ton e-mail pour activer ton compte.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager, EmailVerifier $emailVerifier): Response
    {
        // L'ID est transporté dans l'URL signée.
        $id = $request->query->get('id');

        if ($id === null) {
            return $this->redirectToRoute('app_register');
        }

        $user = $entityManager->getRepository(User::class)->find($id);

        if ($user === null) {
            return $this->redirectToRoute('app_register');
        }

        try {
            // Vérifie signature + expiration, puis active le compte.
            $emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Ton compte est confirmé. Tu peux maintenant te connecter.');

        return $this->redirectToRoute('app_login');
    }
}
