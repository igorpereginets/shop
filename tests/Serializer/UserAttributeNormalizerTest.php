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

    public function testUserIsAnOwner()
    {
        $user = new User();
        $token = $this->getToken();
        $tokenStorage = $this->getTokenStorage();
        $normalizer = $this->getNormalizer();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $normalizer->expects($this->once())
            ->method('normalize')
            ->withAnyParameters()
            ->willReturnArgument(2); //Get context

        $userAttributeNormalizer = new UserAttributeNormalizer($tokenStorage);
        $userAttributeNormalizer->setNormalizer($normalizer);

        $context = $userAttributeNormalizer->normalize($user, null, ['groups' => ['user:get']]);

        $this->assertCount(2, $context);

        $this->assertArrayHasKey('groups', $context);
        $this->assertCount(2, $context['groups']);
        $this->assertEquals(['user:get', 'user:owner:get'], $context['groups']);

        $this->assertArrayHasKey(UserAttributeNormalizer::ALREADY_CALLED, $context);
        $this->assertTrue($context[UserAttributeNormalizer::ALREADY_CALLED]);
    }

    public function testUserIsNotAnOwner()
    {
        $token = $this->getToken();
        $tokenStorage = $this->getTokenStorage();
        $normalizer = $this->getNormalizer();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(new User());

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $normalizer->expects($this->once())
            ->method('normalize')
            ->withAnyParameters()
            ->willReturnArgument(2); //Get context

        $userAttributeNormalizer = new UserAttributeNormalizer($tokenStorage);
        $userAttributeNormalizer->setNormalizer($normalizer);

        $context = $userAttributeNormalizer->normalize(new User());

        $this->assertCount(1, $context);

        $this->assertArrayHasKey(UserAttributeNormalizer::ALREADY_CALLED, $context);
        $this->assertTrue($context[UserAttributeNormalizer::ALREADY_CALLED]);
    }

    public function testIsNotUserInstance()
    {
        $normalizer = $this->getNormalizer();

        $normalizer->expects($this->once())
            ->method('normalize')
            ->withAnyParameters()
            ->willReturnArgument(2); //Get context

        $userAttributeNormalizer = new UserAttributeNormalizer($this->getTokenStorage());
        $userAttributeNormalizer->setNormalizer($normalizer);

        $context = $userAttributeNormalizer->normalize([]); // Not a user

        $this->assertCount(1, $context);

        $this->assertArrayHasKey(UserAttributeNormalizer::ALREADY_CALLED, $context);
        $this->assertTrue($context[UserAttributeNormalizer::ALREADY_CALLED]);
    }

    private function getToken()
    {
        return $this->getMockBuilder(TokenInterface::class)
            ->getMockForAbstractClass();
    }

    private function getTokenStorage()
    {
        return $this->getMockBuilder(TokenStorageInterface::class)
            ->getMockForAbstractClass();
    }

    private function getNormalizer()
    {
        return $this->getMockForAbstractClass(NormalizerInterface::class);
    }
}