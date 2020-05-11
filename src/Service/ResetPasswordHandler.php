<?php

namespace App\Service;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\ResetPassword;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ResetPasswordHandler
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var JWTTokenManagerInterface
     */
    private $tokenManager;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        JWTTokenManagerInterface $tokenManager,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenManager = $tokenManager;
        $this->em = $em;
        $this->validator = $validator;
    }

    public function handle(ResetPassword $data): array
    {
        $user = $data->getUser();
        $this->validator->validate($data);

        if (!$this->passwordEncoder->isPasswordValid($user, $data->getOldPassword())) {
            throw new AccessDeniedException('Old password should match current user\'s password');
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $data->getNewPassword()));
        $this->em->flush();

        return ['token' => $this->tokenManager->create($data->getUser())];
    }

}