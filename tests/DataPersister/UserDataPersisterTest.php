<?php

namespace App\Tests\DataPersister;

use App\DataPersister\UserDataPersister;
use App\Entity\User;
use App\Service\MailerService;
use App\Utils\MDTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDataPersisterTest extends TestCase
{

    public function testObjectIsInstanceOfUser()
    {
        list($passwordEncoder, $em, $tokenGenerator, $mailer) = $this->getArguments();

        $actual = (new UserDataPersister($passwordEncoder, $em, $tokenGenerator, $mailer))->supports(new User);

        $this->assertTrue($actual);
    }

    public function testObjectIsNotInstanceOfUser()
    {
        list($passwordEncoder, $em, $tokenGenerator, $mailer) = $this->getArguments();

        $actual = (new UserDataPersister($passwordEncoder, $em, $tokenGenerator, $mailer))->supports([]);

        $this->assertFalse($actual);
    }

    public function testUserHasPlainPassword()
    {
        $user = (new User())
            ->setPlainPassword('fakePlainPassword')
            ->setActive(true);
        list($passwordEncoder, $em, $tokenGenerator, $mailer) = $this->getArguments();

        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->with($user, $user->getPlainPassword())
            ->willReturn('fakeEncodedPassword');

        $actualUser = (new UserDataPersister($passwordEncoder, $em, $tokenGenerator, $mailer))->persist($user);

        $this->assertNotNull($actualUser);
        $this->assertNotNull($actualUser->getPassword());
        $this->assertNotEquals($user->getPlainPassword(), $actualUser->getPassword());
    }

    public function testUserIsNotActive()
    {
        $user = (new User())
            ->setEmail('fakeEmail@test.com')
            ->setActive(false);
        list($passwordEncoder, $em, $tokenGenerator, $mailer) = $this->getArguments();

        $tokenGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('fakeToken');

        $actualUser = (new UserDataPersister($passwordEncoder, $em, $tokenGenerator, $mailer))->persist($user);

        $this->assertNotNull($actualUser);
        $this->assertNotNull($actualUser->getConfirmationToken());
        $this->assertEquals('fakeToken', $actualUser->getConfirmationToken());
    }

    private function getPasswordEncoder()
    {
        return $this->getMockForAbstractClass(UserPasswordEncoderInterface::class);
    }

    private function getEntityManager()
    {
        return $this->getMockForAbstractClass(EntityManagerInterface::class);
    }

    private function getTokenGenerator()
    {
        return $this->getMockBuilder(MDTokenGenerator::class)
            ->getMock();
    }

    private function getMailer()
    {
        return $this->getMockBuilder(MailerService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getArguments()
    {
        return [
            $this->getPasswordEncoder(),
            $this->getEntityManager(),
            $this->getTokenGenerator(),
            $this->getMailer()
        ];
    }
}