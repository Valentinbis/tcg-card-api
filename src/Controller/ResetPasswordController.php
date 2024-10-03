<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    // Display & process form to request a password reset.
    #[Route('', name: 'app_forgot_password_request', methods: ['POST'])]
    public function request(Request $request, MailerInterface $mailer): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if ($email) {
            $this->logger->info('Password reset request received', ['email' => $email]);
            return $this->processSendingPasswordResetEmail($email, $mailer);
        }

        $this->logger->warning('Password reset request received with no email');
        return $this->json(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
    }

    // Confirmation page after a user has requested a password reset.
    #[Route('/check-email', name: 'app_check_email', methods: ['GET'])]
    public function checkEmail(): JsonResponse
    {
        $this->logger->info('Password reset email sent');
        return new JsonResponse(['message' => 'Reset password email sent.'], Response::HTTP_OK);   
    }

    // Validates and process the reset URL that the user clicked in their email.
    #[Route('/reset', name: 'app_reset_password', methods: ['POST'])]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, string $token = null): JsonResponse
    {
        $token = $request->query->get('token');
        
        if (null === $token) {
            $this->logger->warning('Password reset attempt with no token');
            return new JsonResponse(['message' => 'No reset password token found in the URL.'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->logger->error('Password reset token validation failed', ['token' => $token, 'error' => $e->getReason()]);
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
    
            $this->logger->info('Password successfully reset', ['user_id' => $user->getId()]);

            return new JsonResponse(['message' => 'Password successfully reset'], Response::HTTP_OK);
        }

        $this->logger->warning('Password reset attempt with no password provided');
        return new JsonResponse(['message' => 'No password provided'], Response::HTTP_BAD_REQUEST);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            $this->logger->warning('Password reset email requested for non-existent user', ['email' => $emailFormData]);
            return $this->redirectToRoute('app_check_email');
        }
        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->logger->error('Failed to generate password reset token', ['user_id' => $user->getId(), 'error' => $e->getReason()]);
            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('dev@cashtrack.com', 'Acme Mail Bot'))
            ->to($user->getEmail())
            ->subject('Demande de réinitialisation de mot de passe')
            ->htmlTemplate('email/resetPassword.html.twig')
            ->context([
                'user' => $user,
                'resetLink' => $this->generateUrl('app_reset_password', ['token' => urlencode($resetToken->getToken())]),
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);
        $this->logger->info('Password reset email sent', ['user_id' => $user->getId()]);

        return $this->redirectToRoute('app_check_email');
    }
}
