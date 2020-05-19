<?php

namespace App\Tests\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\DataPersister\UserDataPersister;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @group develop
 */
class UserDataPersisterTest extends TestCase
{
    public function testDoesSupportUserInstance()
    {
        $passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $user = $this->createMock(User::class);
        $decoratedPersister = $this->createMock(DataPersisterInterface::class);

        $decoratedPersister->method('supports')
            ->with($user)
            ->willReturn(true);

        $actual = (new UserDataPersister($decoratedPersister, $passwordEncoder))->supports($user);

        $this->assertTrue($actual);
    }

    public function testPassingNonUserInstance()
    {
        $passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $decoratedPersister = $this->createMock(DataPersisterInterface::class);
        $object = $this->getMockBuilder('NonUserInstance')
            ->setMethods(['setPassword'])
            ->getMock();

        $object->expects($this->never())
            ->method('setPassword');

        (new UserDataPersister($decoratedPersister, $passwordEncoder))->supports($object);
    }

    public function testPlainPasswordIsNull()
    {
        $passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $decoratedPersister = $this->createMock(DataPersisterInterface::class);
        $user = $this->createMock(User::class);

        $user->expects($this->never())
            ->method('setPassword');

        (new UserDataPersister($decoratedPersister, $passwordEncoder))->persist($user);
    }

    public function testPlainPasswordIsNotNull()
    {
        $passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $decoratedPersister = $this->createMock(DataPersisterInterface::class);
        $user = $this->createMock(User::class);

        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->with($user, 'some plain password')
            ->willReturn('Some encoded password');

        $user->method('getPlainPassword')
            ->willReturn('some plain password');

        $user->expects($this->once())
            ->method('setPassword');

        (new UserDataPersister($decoratedPersister, $passwordEncoder))->persist($user);
    }
}