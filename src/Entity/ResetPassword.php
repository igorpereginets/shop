<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\Action\ResetPasswordAction;


/**
 * @ApiResource(
 *     itemOperations={
 *          "put"={
 *              "security"="is_granted('ROLE_ADMIN') or object.getUser() == user",
 *              "path"="/users/{id}/reset-password",
 *              "controller"=ResetPasswordAction::class,
 *          }
 *     },
 *     collectionOperations={}
 * )
 */
class ResetPassword
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="5", max="255")
     * @Assert\Regex(
     *     pattern="/^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z]).{5,}$/",
     *     message="Password should contain at least 1 uppercase, 1 lowercase letter and 1 digit."
     * )
     * @Groups({"user:password:reset"})
     */
    private $newPassword;

    /**
     * @Assert\Expression(expression="this.getNewPassword() == this.getRetypedNewPassword()")
     * @Groups({"user:password:reset"})
     */
    private $retypedNewPassword;

    /**
     * @Groups({"user:password:reset"})
     */
    private $oldPassword;

    /**
     * @ApiProperty(identifier=true)
     */
    private $user;

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }

    public function getRetypedNewPassword(): ?string
    {
        return $this->retypedNewPassword;
    }

    public function setRetypedNewPassword(?string $retypedNewPassword): void
    {
        $this->retypedNewPassword = $retypedNewPassword;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(?string $oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}