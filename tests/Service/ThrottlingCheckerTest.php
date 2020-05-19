<?php

namespace App\Tests\Service;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Service\ThrottlingChecker;
use App\Repository\ResetPasswordRequestRepository;
use PHPUnit\Framework\TestCase;

class ThrottlingCheckerTest extends TestCase
{

    public function testUserHasNoLastRequests()
    {
        $repository = $this->createMock(ResetPasswordRequestRepository::class);

        $actual = (new ThrottlingChecker($repository))->hasThrottling(new User());

        $this->assertFalse($actual);
    }

    public function testUserHasLastRequestWithCreatedDateOneHourAgo()
    {
        $repository = $this->createMock(ResetPasswordRequestRepository::class);
        $lastResetRequest = $this->createMock(ResetPasswordRequest::class);

        $lastResetRequest->method('getCreatedAt')
            ->willReturn(new \DateTimeImmutable('-1 hour'));

        $repository->method('findRecentNotExpiredRequest')
            ->willReturn($lastResetRequest);

        $result = (new ThrottlingChecker($repository))->hasThrottling(new User());

        $this->assertFalse($result);
    }

    public function testUserHasLastRequestWithCreatedDateLessThenOneHourAgo()
    {
        $repository = $this->createMock(ResetPasswordRequestRepository::class);
        $lastResetRequest = $this->createMock(ResetPasswordRequest::class);

        $lastResetRequest->method('getCreatedAt')
            ->willReturn(new \DateTimeImmutable('-30 minutes'));

        $repository->method('findRecentNotExpiredRequest')
            ->willReturn($lastResetRequest);

        $result = (new ThrottlingChecker($repository))->hasThrottling(new User());

        $this->assertTrue($result);
    }
}