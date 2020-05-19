<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\DTO\ResetPassword;
use App\Entity\DTO\ResetPasswordRequest;
use App\Entity\User;
use App\Exception\ResetPassword\TooManyRequestsException;
use App\Service\ThrottlingChecker;
use App\Service\MailerService;
use App\Utils\MDTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordRequestController extends AbstractController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
    }

    public function request(ResetPasswordRequest $data, ThrottlingChecker $throttlingChecker, MailerService $mailer, MDTokenGenerator $tokenGenerator)
    {
        $this->validator->validate($data);

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data->getEmail()]);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        if ($throttlingChecker->hasThrottling($user)) {
            throw new TooManyRequestsException();
        }

        $token = $tokenGenerator->generate();

        $newRequest = (new \App\Entity\ResetPasswordRequest())
            ->setUser($user)
            ->setToken($token)
            ->setExpiredAt(new \DateTimeImmutable(\sprintf('+%d seconds', \App\Entity\ResetPasswordRequest::EXPIRES_IN)));

        $this->entityManager->persist($newRequest);
        $this->entityManager->flush();

        $mailer->sendResetPasswordToken($data->getEmail(), $token);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function reset(ResetPassword $data, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->validator->validate($data);

        /** @var \App\Entity\ResetPasswordRequest $request */
        $requestRepository = $this->entityManager->getRepository(ResetPasswordRequest::class);
        $request = $requestRepository->findOneBy(['token' => $data->getToken()]);

        if (!$request || $request->isExpired()) {
            throw new BadRequestHttpException();
        }

        $user = $request->getUser();

        $user->setPassword($passwordEncoder->encodePassword($user, $data->getPassword()));
        $this->entityManager->remove($request);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}