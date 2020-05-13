<?php


namespace App\Entity\DTO;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\UserConfirmationController;

/**
 * @ApiResource(
 *     itemOperations={},
 *     collectionOperations={
 *          "post"={
 *              "path"="/users/confirm",
 *              "controller": UserConfirmationController::class
 *          }
 *     }
 * )
 */
class UserConfirmation
{
    /**
     * @Assert\NotBlank()
     */
    private $confirmationToken;

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }


}