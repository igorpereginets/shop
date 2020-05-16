<?php

namespace App\Tests\Service;

use App\Entity\DTO\ResetPassword;
use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordHandlerTest extends TestCase
{

    public function testSuccessfulReset()
    {
        $fakeToken = 'fakeToken';
        $fakePassword = 'fakePassword';
        $fakeEncodedPassword = 'encodedFakePassword';
        $user = new User();
        $resetPassword = (new ResetPassword())
            ->setToken($fakeToken)
            ->setPassword($fakePassword);

        $request = $this->getResetPasswordRequest();
        $repository = $this->getRepository();
        $em = $this->getEntityManager();
        $passwordEncoder = $this->getPasswordEncoder();

        $request->expects($this->once())
            ->method('isExpired')
            ->willReturn(false);

        $request->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => $fakeToken])
            ->willReturn($request);

        $em->expects($this->once())
            ->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($repository);

        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->with($user, $fakePassword)
            ->willReturn($fakeEncodedPassword);

        (new \App\Service\ResetPasswordHandler($em, $passwordEncoder))
            ->handleReset($resetPassword);

        $this->assertEquals($fakeEncodedPassword, $user->getPassword());
    }

    public function testNoRequestFoundException()
    {
        $fakeToken = 'fakeToken';
        $fakePassword = 'fakePassword';
        $resetPassword = (new ResetPassword())
            ->setToken($fakeToken)
            ->setPassword($fakePassword);

        $repository = $this->getRepository();
        $em = $this->getEntityManager();

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => $fakeToken])
            ->willReturn(null);

        $em->expects($this->once())
            ->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($repository);

        $this->expectException(BadRequestHttpException::class);
        (new \App\Service\ResetPasswordHandler($em, $this->getPasswordEncoder()))
            ->handleReset($resetPassword);
    }

    public function testRequestExpiredException()
    {
        $fakeToken = 'fakeToken';
        $fakePassword = 'fakePassword';
        $resetPassword = (new ResetPassword())
            ->setToken($fakeToken)
            ->setPassword($fakePassword);

        $request = $this->getResetPasswordRequest();
        $repository = $this->getRepository();
        $em = $this->getEntityManager();

        $request->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => $fakeToken])
            ->willReturn($request);

        $em->expects($this->once())
            ->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($repository);

        $this->expectException(BadRequestHttpException::class);
        (new \App\Service\ResetPasswordHandler($em, $this->getPasswordEncoder()))
            ->handleReset($resetPassword);
    }

    private function getPasswordEncoder()
    {
        return $this->getMockBuilder(UserPasswordEncoderInterface::class)
            ->getMockForAbstractClass();
    }

    private function getEntityManager()
    {
        return $this->getMockBuilder(EntityManagerInterface::class)
            ->getMockForAbstractClass();
    }

    private function getRepository()
    {
        return $this->getMockBuilder(ResetPasswordRequestRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getResetPasswordRequest()
    {
        return $this->getMockBuilder(ResetPasswordRequest::class)
            ->getMock();
    }
}