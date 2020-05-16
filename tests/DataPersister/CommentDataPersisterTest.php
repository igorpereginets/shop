<?php

namespace App\Tests\DataPersister;

use App\DataPersister\CommentDataPersister;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommentDataPersisterTest extends TestCase
{
    public function testCommentHasUser()
    {
        $user = new User();
        $comment = (new Comment())->setUser($user);

        $actualComment = (new CommentDataPersister($this->getTokenStorage(), $this->getEntityManager()))
            ->persist($comment);

        $this->assertSame($comment, $actualComment);
        $this->assertSame($user, $actualComment->getUser());
    }

    public function testCommentHasNoUser()
    {
        $user = new User();
        $comment = new Comment();
        $token = $this->getToken();
        $tokenStorage = $this->getTokenStorage();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $actualComment = (new CommentDataPersister($tokenStorage, $this->getEntityManager()))->persist($comment);

        $this->assertSame($comment, $actualComment);
        $this->assertSame($user, $actualComment->getUser());
    }

    private function getEntityManager()
    {
        return $this->getMockForAbstractClass(EntityManagerInterface::class);
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
}