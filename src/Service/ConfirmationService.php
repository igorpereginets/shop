<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class ConfirmationService
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $manager)
    {
        $this->userRepository = $userRepository;
        $this->manager = $manager;
    }

    /**
     * @param string $confirmationToken
     * @throws EntityNotFoundException
     */
    public function confirmUser(string $confirmationToken): void
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['confirmationToken' => $confirmationToken]);

        if (!$user) {
            throw new EntityNotFoundException();
        }

        $user->setActive(true);
        $user->setConfirmationToken(null);

        $this->manager->flush();
    }
}