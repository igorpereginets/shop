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
        $user = new User();
        $user->setActive(false);

        $this->assertFalse($user->isActive());

        $this->expectException(DisabledException::class);
        (new UserActiveChecker())->checkPreAuth($user);
    }

    public function testPreAuthActiveUser()
    {
        $user = new User();
        $user->setActive(true);

        $this->assertTrue($user->isActive());

        $this->assertNull((new UserActiveChecker())->checkPreAuth($user));
    }

    public function testPreAuthIfArgumentIsNotInstanceOfUser()
    {
        $userMock = $this->getMockBuilder(UserInterface::class)
            ->getMockForAbstractClass();

        $this->assertFalse($userMock instanceof User);

        $this->assertNull((new UserActiveChecker())->checkPreAuth($userMock));
    }
}