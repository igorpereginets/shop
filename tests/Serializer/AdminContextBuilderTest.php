<?php

namespace App\Tests\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Serializer\AdminContextBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminContextBuilderTest extends TestCase
{

    public function testUserHasAdminRole()
    {
        $serializer = $this->getSerializer();
        $authorizationChecker = $this->getAuthorizationChecker();

        $serializer->expects($this->once())
            ->method('createFromRequest')
            ->withAnyParameters()
            ->willReturn(['groups' => []]);

        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $context = (new AdminContextBuilder($serializer, $authorizationChecker))
            ->createFromRequest($this->getRequest(), true, null);

        $this->assertArrayHasKey('groups', $context);
        $this->assertCount(1, $context['groups']);
        $this->assertEquals('admin:get', $context['groups'][0]);
    }

    public function testUserDoNotHaveAdminRole()
    {
        $serializer = $this->getSerializer();
        $authorizationChecker = $this->getAuthorizationChecker();

        $serializer->expects($this->once())
            ->method('createFromRequest')
            ->withAnyParameters()
            ->willReturn(['groups' => []]);

        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $context = (new AdminContextBuilder($serializer, $authorizationChecker))
            ->createFromRequest($this->getRequest(), true, null);

        $this->assertArrayHasKey('groups', $context);
        $this->assertEmpty($context['groups']);
    }

    public function testInDenormalizationMode()
    {
        $serializer = $this->getSerializer();
        $authorizationChecker = $this->getAuthorizationChecker();

        $serializer->expects($this->once())
            ->method('createFromRequest')
            ->withAnyParameters()
            ->willReturn(['groups' => []]);

        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $context = (new AdminContextBuilder($serializer, $authorizationChecker))
            ->createFromRequest($this->getRequest(), false, null);

        $this->assertArrayHasKey('groups', $context);
        $this->assertEmpty($context['groups']);
    }

    private function getSerializer()
    {
        return $this->getMockBuilder(SerializerContextBuilderInterface::class)
            ->getMockForAbstractClass();
    }

    private function getAuthorizationChecker()
    {
        return $this->getMockBuilder(AuthorizationCheckerInterface::class)
            ->getMockForAbstractClass();
    }

    private function getRequest()
    {
        return $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}