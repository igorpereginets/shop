<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserActiveChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserActiveCheckerTest extends TestCase
{

    public function testPreAuthNonActiveUser()
    {
        $user = $this->createMock(User::class);

        $this->expectException(DisabledException::class);
        (new UserActiveChecker())->checkPreAuth($user);
    }

    public function testPreAuthActiveUser()
    {
        $user = $this->createMock(User::class);

        $user->method('isActive')->willReturn(true);

        $this->assertNull((new UserActiveChecker())->checkPreAuth($user));
    }

    public function testPreAuthIfArgumentIsNotInstanceOfUser()
    {
        $userMock = $this->getMockBuilder(UserInterface::class)->getMock();

        $this->assertNull((new UserActiveChecker())->checkPreAuth($userMock));
    }
}