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
        $user = (new User())->setActive(false)->setConfirmationToken($confirmationToken);

        $userRepository = $this->getUserRepository();
        $entityManager = $this->getEntityManager();

        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['confirmationToken' => $confirmationToken])
            ->willReturn($user);

        (new ConfirmationService($userRepository, $entityManager))->confirmUser($confirmationToken);

        $this->assertTrue($user->isActive());
        $this->assertNull($user->getConfirmationToken());
    }

    public function testNotFoundUser()
    {
        $confirmationToken = 'fakeConfirmationToken';

        $userRepository = $this->getUserRepository();

        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['confirmationToken' => $confirmationToken])
            ->willReturn(null);

        $this->expectException(EntityNotFoundException::class);
        (new ConfirmationService($userRepository, $this->getEntityManager()))
            ->confirmUser($confirmationToken);
    }

    private function getUserRepository()
    {
        return $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getEntityManager()
    {
        return $this->getMockBuilder(EntityManagerInterface::class)
            ->getMockForAbstractClass();
    }
}