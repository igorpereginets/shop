<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ConfirmationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use PHPUnit\Framework\TestCase;

class ConfirmationServiceTest extends TestCase
{

    public function testSuccessfulConfirmUser()
    {
        $confirmationToken = 'fakeConfirmationToken';
        $userRepository = $this->createMock(UserRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $user = $this->createMock(User::class);

        $userRepository->method('findOneBy')
            ->with(['confirmationToken' => $confirmationToken])
            ->willReturn($user);

        $user->expects($this->once())
            ->method('setActive')
            ->with(true);

        $user->expects($this->once())
            ->method('setConfirmationToken')
            ->with(null);

        (new ConfirmationService($userRepository, $entityManager))->confirmUser($confirmationToken);
    }

    public function testNotFoundUser()
    {
        $confirmationToken = 'fakeConfirmationToken';
        $userRepository = $this->createMock(UserRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $this->expectException(EntityNotFoundException::class);
        (new ConfirmationService($userRepository, $entityManager))
            ->confirmUser($confirmationToken);
    }
}