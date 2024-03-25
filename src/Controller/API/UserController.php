<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{

    #[Route('/api/me')]
    #[IsGranted("ROLE_USER")]
    public function me()
    {
        $user = $this->getUser();
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.show']
        ]);
    }

}