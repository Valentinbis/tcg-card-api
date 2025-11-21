<?php

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequestDTO
{
    #[Assert\NotBlank(message: "L'email est requis")]
    #[Assert\Email(message: "L'email n'est pas valide")]
    /** @phpstan-ignore-next-line */
    #[OA\Property(example: 'newuser@example.com')]
    public string $email;

    #[Assert\NotBlank(message: 'Le mot de passe est requis')]
    #[Assert\Length(min: 6, minMessage: 'Le mot de passe doit faire au moins {{ limit }} caractères')]
    /** @phpstan-ignore-next-line */
    #[OA\Property(example: 'securePassword123')]
    public string $password;

    #[Assert\NotBlank(message: "Le nom d'utilisateur est requis")]
    #[Assert\Length(min: 3, max: 50)]
    /** @phpstan-ignore-next-line */
    #[OA\Property(example: 'johndoe')]
    public string $username;
}
