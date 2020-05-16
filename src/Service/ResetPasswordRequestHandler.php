<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\ResetPassword\TooManyRequestsException;
use App\Helper\ResetPasswordHelper;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResetPasswordRequestHandler
{
    /**
     * @var MailerService
     */
    private $mailer;
    /**
     * @var ResetPasswordHelper
     */
    private $helper;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        UserRepository $userRepository,
        MailerService $mailer,
        ResetPasswordHelper $helper
    )
    {
        $this->mailer = $mailer;
        $this->helper = $helper;
        $this->userRepository = $userRepository;
    }

    public function handleRequest(string $email)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        if ($this->helper->hasUserHitThrottling($user)) {
            throw new TooManyRequestsException();
        }

        $token = $this->helper->persistRequest($user);
        $this->mailer->sendResetPasswordToken($email, $token);
    }
}