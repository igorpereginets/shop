<?php

namespace App\Tests\Helper;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Helper\ResetPasswordHelper;
use App\Repository\ResetPasswordRequestRepository;
use App\Utils\MDTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ResetPasswordHelperTest extends TestCase
{

    public function testUserHasNoLastRequests()
    {
        $user = new User();
        $em = $this->getEntityManager();
        $repository = $this->getRepository();

        $repository->expects($this->once())
            ->method('findRecentNotExpiredRequest')
            ->with($user)
            ->willReturn(null);

        $em->expects($this->once())
            ->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($repository);

        $result = (new ResetPasswordHelper($em, $this->getTokenGenerator()))->hasUserHitThrottling($user);

        $this->assertFalse($result);
    }

    public function testUserHasLastRequestWithCreatedDateOneHourAgo()
    {
        $user = new User();
        $em = $this->getEntityManager();
        $repository = $this->getRepository();
        $lastResetRequest = (new ResetPasswordRequest())
            ->setCreatedAt(new \DateTimeImmutable('-1 hour'));

        $repository->expects($this->once())
            ->method('findRecentNotExpiredRequest')
            ->with($user)
            ->willReturn($lastResetRequest);

        $em->expects($this->once())
            ->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($repository);

        $result = (new ResetPasswordHelper($em, $this->getTokenGenerator()))->hasUserHitThrottling($user);

        $this->assertFalse($result);
    }

    public function testUserHasLastRequestWithCreatedDateLessThenOneHourAgo()
    {
        $user = new User();
        $em = $this->getEntityManager();
        $repository = $this->getRepository();
        $lastResetRequest = (new ResetPasswordRequest())
            ->setCreatedAt(new \DateTimeImmutable('-30 minutes'));

        $repository->expects($this->once())
            ->method('findRecentNotExpiredRequest')
            ->with($user)
            ->willReturn($lastResetRequest);

        $em->expects($this->once())
            ->method('getRepository')
            ->with(ResetPasswordRequest::class)
            ->willReturn($repository);

        $result = (new ResetPasswordHelper($em, $this->getTokenGenerator()))->hasUserHitThrottling($user);

        $this->assertTrue($result);
    }

    private function getTokenGenerator()
    {
        return $this->getMockBuilder(MDTokenGenerator::class)
            ->getMock();
    }

    private function getEntityManager()
    {
        return $this->getMockForAbstractClass(EntityManagerInterface::class);
    }

    private function getRepository()
    {
        return $this->getMockBuilder(ResetPasswordRequestRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

    }
}