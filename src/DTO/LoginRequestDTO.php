<?php

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class LoginRequestDTO
{
    #[Assert\NotBlank(message: "L'email est requis")]
    #[Assert\Email(message: "L'email n'est pas valide")]
    /** @phpstan-ignore-next-line */
    #[OA\Property(example: 'test@test.com')]
    public string $email;

    #[Assert\NotBlank(message: 'Le mot de passe est requis')]
    /** @phpstan-ignore-next-line */
    #[OA\Property(example: 'password')]
    public string $password;
}
