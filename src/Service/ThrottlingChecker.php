<?php

namespace App\Service;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;

class ThrottlingChecker
{
    /**
     * @var ResetPasswordRequestRepository
     */
    private $repository;

    public function __construct(ResetPasswordRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function hasThrottling(User $user): bool
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