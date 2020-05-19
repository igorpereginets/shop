<?php

namespace App\Tests\Serializer;

use App\Entity\User;
use App\Serializer\UserAttributeNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserAttributeNormalizerTest extends TestCase
{

    public function testIsNotUserInstance()
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $object = $this->getMockBuilder('NonUserInterfaceInstance')->getMock();

        $normalizer->method('normalize')->willReturnArgument(2);

        $userAttributeNormalizer = new UserAttributeNormalizer($tokenStorage);
        $userAttributeNormalizer->setNormalizer($normalizer);

        $context = $userAttributeNormalizer->normalize($object);

        $this->assertCount(1, $context);
        $this->assertArrayHasKey(UserAttributeNormalizer::ALREADY_CALLED, $context);
        $this->assertTrue($context[UserAttributeNormalizer::ALREADY_CALLED]);
    }

    public function testUserIsNotAuthorized()
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $user = $this->createMock(User::class);

        $normalizer->method('normalize')->willReturnArgument(2);

        $userAttributeNormalizer = new UserAttributeNormalizer($tokenStorage);
        $userAttributeNormalizer->setNormalizer($normalizer);

        $context = $userAttributeNormalizer->normalize($user);

        $this->assertCount(1, $context);
        $this->assertArrayHasKey(UserAttributeNormalizer::ALREADY_CALLED, $context);
        $this->assertTrue($context[UserAttributeNormalizer::ALREADY_CALLED]);
    }

    public function testAuthorizedUserIsNotInstanceOfUser()
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(User::class);

        $token->method('getUser')->willReturn('NonExistedClass');
        $tokenStorage->method('getToken')->willReturn($token);
        $normalizer->method('normalize')->willReturnArgument(2);

        $userAttributeNormalizer = new UserAttributeNormalizer($tokenStorage);
        $userAttributeNormalizer->setNormalizer($normalizer);

        $context = $userAttributeNormalizer->normalize($user);

        $this->assertCount(1, $context);
        $this->assertArrayHasKey(UserAttributeNormalizer::ALREADY_CALLED, $context);
        $this->assertTrue($context[UserAttributeNormalizer::ALREADY_CALLED]);
    }

    public function testUserIsNotAnOwner()
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(User::class);

        $token->method('getUser')->willReturn(new User());
        $tokenStorage->method('getToken')->willReturn($token);
        $normalizer->method('normalize')->willReturnArgument(2);

        $userAttributeNormalizer = new UserAttributeNormalizer($tokenStorage);
        $userAttributeNormalizer->setNormalizer($normalizer);

        $context = $userAttributeNormalizer->normalize($user);

        $this->assertCount(1, $context);
        $this->assertArrayHasKey(UserAttributeNormalizer::ALREADY_CALLED, $context);
        $this->assertTrue($context[UserAttributeNormalizer::ALREADY_CALLED]);
    }

    public function testUserIsAnOwner()
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(User::class);

        $token->method('getUser')->willReturn($user);
        $tokenStorage->method('getToken')->willReturn($token);
        $normalizer->method('normalize')->willReturnArgument(2);

        $userAttributeNormalizer = new UserAttributeNormalizer($tokenStorage);
        $userAttributeNormalizer->setNormalizer($normalizer);

        $context = $userAttributeNormalizer->normalize($user);

        $this->assertCount(2, $context);
        $this->assertArrayHasKey('groups', $context);
        $this->assertContains('user:owner:get', $context['groups']);
        $this->assertArrayHasKey(UserAttributeNormalizer::ALREADY_CALLED, $context);
        $this->assertTrue($context[UserAttributeNormalizer::ALREADY_CALLED]);
    }
}