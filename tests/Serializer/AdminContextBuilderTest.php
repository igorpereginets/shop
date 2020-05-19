<?php

namespace App\Tests\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Serializer\AdminContextBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminContextBuilderTest extends TestCase
{

    public function testInDenormalizationMode()
    {
        $serializer = $this->createMock(SerializerContextBuilderInterface::class);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $request = $this->createMock(Request::class);

        $serializer->method('createFromRequest')->willReturn(['groups' => []]);
        $authorizationChecker->method('isGranted')->willReturn(true);

        $context = (new AdminContextBuilder($serializer, $authorizationChecker))
            ->createFromRequest($request, false, null);

        $this->assertArrayHasKey('groups', $context);
        $this->assertEmpty($context['groups']);
    }

    public function testIfThereIsNoGroupsArray()
    {
        $serializer = $this->createMock(SerializerContextBuilderInterface::class);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $request = $this->createMock(Request::class);

        $serializer->method('createFromRequest')->willReturn([]);
        $authorizationChecker->method('isGranted')->willReturn(true);

        $context = (new AdminContextBuilder($serializer, $authorizationChecker))
            ->createFromRequest($request, true, null);

        $this->assertArrayNotHasKey('groups', $context);
    }

    public function testUserDoNotHaveAdminRole()
    {
        $serializer = $this->createMock(SerializerContextBuilderInterface::class);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $request = $this->createMock(Request::class);

        $serializer->method('createFromRequest')->willReturn(['groups' => []]);

        $context = (new AdminContextBuilder($serializer, $authorizationChecker))
            ->createFromRequest($request, true, null);

        $this->assertArrayHasKey('groups', $context);
        $this->assertEmpty($context['groups']);
    }

    public function testUserHasAdminRole()
    {
        $serializer = $this->createMock(SerializerContextBuilderInterface::class);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $request = $this->createMock(Request::class);

        $serializer->method('createFromRequest')->willReturn(['groups' => []]);
        $authorizationChecker->method('isGranted')->willReturn(true);

        $context = (new AdminContextBuilder($serializer, $authorizationChecker))
            ->createFromRequest($request, true, null);

        $this->assertArrayHasKey('groups', $context);
        $this->assertCount(1, $context['groups']);
        $this->assertEquals('admin:get', $context['groups'][0]);
    }
}