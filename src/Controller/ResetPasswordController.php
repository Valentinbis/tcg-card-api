<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    // Display & process form to request a password reset.
    #[Route('', name: 'app_forgot_password_request', methods: ['POST'])]
    public function request(Request $request, MailerInterface $mailer): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if ($email) {
            return $this->processSendingPasswordResetEmail($email, $mailer);
        }

        return $this->json(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
    }

    // Confirmation page after a user has requested a password reset.
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): JsonResponse
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return new JsonResponse(['resetToken' => $resetToken], Response::HTTP_OK);
   
    }

    // Validates and process the reset URL that the user clicked in their email.
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, string $token = null): JsonResponse
    {
        if ($token) {
            $this->storeTokenInSession($token);
    
            return new JsonResponse(['message' => 'Token stored in session'], Response::HTTP_OK);
        }
    
        $token = $this->getTokenFromSession();
        if (null === $token) {
            return new JsonResponse(['message' => 'No reset password token found in the URL or in the session.'], Response::HTTP_NOT_FOUND);
        }
    
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return new JsonResponse([
                'message' => sprintf(
                    '%s - %s',
                    ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                    $e->getReason()
                )
            ], Response::HTTP_BAD_REQUEST);
        }
    
        $data = json_decode($request->getContent(), true);
    
        if ($data['password']) {
            $this->resetPasswordHelper->removeResetRequest($token);
    
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $data['password']
            );
    
            $user->setPassword($encodedPassword);
            $this->entityManager->flush();
    
            $this->cleanSessionAfterReset();
    
            return new JsonResponse(['message' => 'Password successfully reset'], Response::HTTP_OK);
        }
    
        return new JsonResponse(['message' => 'No password provided'], Response::HTTP_BAD_REQUEST);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }
        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('dev@cashtrack.com', 'Acme Mail Bot'))
            ->to($user->getEmail())
            ->subject('Demande de réinitialisation de mot de passe')
            ->htmlTemplate('email/resetPassword.html.twig')
            ->context([
                'user' => $user,
                'resetToken' => $resetToken,
                'resetLink' => $this->generateUrl('app_reset_password', ['token' => $resetToken->getToken()]),
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
