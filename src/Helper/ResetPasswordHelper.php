<?php

namespace App\Helper;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

class ResetPasswordHelper
{
    /**
     * @var ResetPasswordRequestRepository
     */
    private $repository;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->repository = $manager->getRepository(ResetPasswordRequest::class);
        $this->manager = $manager;
    }

    public function generateToken(): string
    {
        return md5(random_bytes(20));
    }

    public function persistRequest(User $user, string $token): void
    {
        $newRequest = (new ResetPasswordRequest())
            ->setUser($user)
            ->setToken($token)
            ->setExpiredAt(new \DateTimeImmutable(\sprintf('+%d seconds', ResetPasswordRequest::EXPIRES_IN)));

        $this->manager->persist($newRequest);
        $this->manager->flush();
    }

    public function hasUserHitThrottling(User $user): bool
    {
        /** @var ResetPasswordRequest $lastRequest */
        $lastRequest = $this->repository->findRecentNotExpiredRequest($user);

        if (null !== $lastRequest) {
            $createdAt = clone $lastRequest->getCreatedAt();

            if (new \DateTime('now') < $createdAt->add(new \DateInterval(sprintf('PT%dS', ResetPasswordRequest::EXPIRES_IN)))) {
                return true;
            }
        }

        return false;
    }
}