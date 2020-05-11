<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\ResetPassword;
use App\Repository\UserRepository;

class ResetPasswordDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $resetPassword = new ResetPassword();
        $resetPassword->setUser($this->userRepository->find($id));

        return $resetPassword;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ResetPassword::class === $resourceClass;
    }
}