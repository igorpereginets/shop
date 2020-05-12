<?php

namespace App\Entity\DTO;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={},
 *     collectionOperations={
 *          "post"={
 *              "path"="/reset-password/request",
 *              "controller"="App\Controller\ResetPasswordRequestController::request"
 *          }
 *     }
 * )
 */
class ResetPasswordRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}