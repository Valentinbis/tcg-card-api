<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\APIAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        APIAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        $user = new User();

        $data = $serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $validator->validate($data);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        //set user data
        $user = $data;

        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $data->getPassword()
            ),
        );

        $entityManager->persist($user);
        $entityManager->flush();

        $authenticateUser = $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );

        if ($authenticateUser instanceof Response) {
            return $authenticateUser;
        }

        return $this->json($user,
            Response::HTTP_OK,
            [],
            [
                'groups' => ['user.show']
            ]
        );
    }
}
