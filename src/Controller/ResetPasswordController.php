<?php

namespace App\Controller;

use App\Attribute\LogSecurity;
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
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'app_forgot_password_request', methods: ['POST'])]
    #[LogSecurity('password_reset_request', 'Password reset requested')]
    public function request(Request $request, MailerInterface $mailer): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if ($email) {
            return $this->processSendingPasswordResetEmail($email, $mailer);
        }
        
        return $this->json(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/check-email', name: 'app_check_email', methods: ['GET'])]
    public function checkEmail(): JsonResponse
    {
        return new JsonResponse(['message' => 'Reset password email sent.'], Response::HTTP_OK);   
    }

    #[Route('/reset', name: 'app_reset_password', methods: ['POST'])]
    #[LogSecurity('password_reset', 'Password reset attempt')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, string $token = null): JsonResponse
    {
        $token = $request->query->get('token');
        
        if (null === $token) {
            return new JsonResponse(['message' => 'No reset password token found in the URL.'], Response::HTTP_NOT_FOUND);
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
    
        if ($data['password'] ?? null) {
            try {
                $this->resetPasswordHelper->removeResetRequest($token);
        
                $encodedPassword = $passwordHasher->hashPassword(
                    $user,
                    $data['password']
                );
        
                $user->setPassword($encodedPassword);
                $this->entityManager->flush();
        
                $this->cleanSessionAfterReset();

                return new JsonResponse(['message' => 'Password successfully reset'], Response::HTTP_OK);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Failed to reset password'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        return new JsonResponse(['message' => 'No password provided'], Response::HTTP_BAD_REQUEST);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }
        
        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address('dev@tcgcard.com', 'Acme Mail Bot'))
                ->to($user->getEmail())
                ->subject('Demande de réinitialisation de mot de passe')
                ->htmlTemplate('email/resetPassword.html.twig')
                ->context([
                    'user' => $user,
                    'resetLink' => $this->generateUrl('app_reset_password', ['token' => urlencode($resetToken->getToken())]),
                ])
            ;

            $mailer->send($email);
            $this->setTokenObjectInSession($resetToken);

            return $this->redirectToRoute('app_check_email');
        } catch (\Exception $e) {
            return $this->redirectToRoute('app_check_email');
        }
    }
}
