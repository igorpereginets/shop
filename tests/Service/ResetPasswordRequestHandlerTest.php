<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Exception\ResetPassword\TooManyRequestsException;
use App\Helper\ResetPasswordHelper;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\ResetPasswordRequestHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResetPasswordRequestHandlerTest extends TestCase
{

    public function testSuccessRequest()
    {
        $fakeEmail = 'fakeEmail@test.com';
        $fakeToken = 'fakeToken';
        $user = (new User())->setEmail($fakeEmail);

        $userRepository = $this->getUserRepository();
        $helper = $this->getHelper();
        $mailer = $this->getMailer();

        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $fakeEmail])
            ->willReturn($user);

        $helper->expects($this->once())
            ->method('hasUserHitThrottling')
            ->with($user)
            ->willReturn(false);

        $helper->expects($this->once())
            ->method('persistRequest')
            ->with($user)
            ->willReturn($fakeToken);

        (new ResetPasswordRequestHandler($userRepository, $mailer, $helper))->handleRequest($fakeEmail);
    }

    public function testUserNotFoundException()
    {
        $fakeEmail = 'fakeEmail@test.com';

        $userRepository = $this->getUserRepository();

        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $fakeEmail])
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        (new ResetPasswordRequestHandler($userRepository, $this->getMailer(), $this->getHelper()))->handleRequest($fakeEmail);
    }

    public function testUserHitThrottlingException()
    {
        $fakeEmail = 'fakeEmail@test.com';
        $user = (new User())->setEmail($fakeEmail);

        $userRepository = $this->getUserRepository();
        $helper = $this->getHelper();

        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $fakeEmail])
            ->willReturn($user);

        $helper->expects($this->once())
            ->method('hasUserHitThrottling')
            ->with($user)
            ->willReturn(true);

        $this->expectException(TooManyRequestsException::class);
        (new ResetPasswordRequestHandler($userRepository, $this->getMailer(), $helper))->handleRequest($fakeEmail);
    }

    private function getUserRepository()
    {
        return $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getHelper()
    {
        return $this->getMockBuilder(ResetPasswordHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMailer()
    {
        return $this->getMockBuilder(MailerService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}