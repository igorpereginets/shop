<?php

namespace App\Entity\DTO;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={},
 *     collectionOperations={
 *          "post"={
 *              "path"="/reset-password/reset",
 *              "controller"="App\Controller\ResetPasswordRequestController::reset"
 *          }
 *     }
 * )
 */
class ResetPassword
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=255)
     * @Assert\Regex(
     *     pattern="/^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z]).{5,}$/",
     *     message="Password should contain at least 1 uppercase, 1 lowercase letter and 1 digit."
     * )
     */
    private $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Expression(
     *     expression="this.getPassword() === this.getRepeatedPassword()",
     *     message="Retyped password does not match."
     * )
     */
    private $repeatedPassword;

    /**
     * @Assert\NotBlank()
     */
    private $token;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRepeatedPassword(): ?string
    {
        return $this->repeatedPassword;
    }

    public function setRepeatedPassword(string $repeatedPassword): self
    {
        $this->repeatedPassword = $repeatedPassword;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}