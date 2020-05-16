<?php

namespace App\Service;

use App\Entity\DTO\ResetPassword;
use App\Entity\ResetPasswordRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function handleReset(ResetPassword $resetPassword)
    {
        /** @var ResetPasswordRequest $request */
        $requestRepository = $this->em->getRepository(ResetPasswordRequest::class);
        $request = $requestRepository->findOneBy(['token' => $resetPassword->getToken()]);

        if (!$request || $request->isExpired()) {
            throw new BadRequestHttpException();
        }

        $user = $request->getUser();

        $user->setPassword($this->passwordEncoder->encodePassword($user, $resetPassword->getPassword()));
        $this->em->remove($request);
        $this->em->flush();
    }
}