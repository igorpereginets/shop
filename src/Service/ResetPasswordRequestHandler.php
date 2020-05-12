<?php

namespace App\Service;

use App\Entity\DTO\ResetPassword;
use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Exception\ResetPassword\TooManyRequestsException;
use App\Helper\ResetPasswordHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordRequestHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var MailerService
     */
    private $mailer;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var ResetPasswordHelper
     */
    private $helper;

    public function __construct(
        EntityManagerInterface $em,
        MailerService $mailer,
        UserPasswordEncoderInterface $passwordEncoder,
        ResetPasswordHelper $helper
    )
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->passwordEncoder = $passwordEncoder;
        $this->helper = $helper;
    }

    public function handleRequest(\App\Entity\DTO\ResetPasswordRequest $passwordRequest)
    {
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $passwordRequest->getEmail()]);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        if ($this->helper->hasUserHitThrottling($user)) {
            throw new TooManyRequestsException();
        }

        $token = $this->helper->generateToken();
        $this->helper->persistRequest($user, $token);
        $this->mailer->sendConfirmationToken($passwordRequest->getEmail(), $token);
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