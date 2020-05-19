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

    public function testDoesSupportCommentInstance()
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $comment = $this->createMock(Comment::class);

        $actual = (new CommentDataPersister($tokenStorage, $em))->supports($comment);

        $this->assertTrue($actual);
    }

    public function testDoesNotSupportNonCommentInstance()
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $actual = (new CommentDataPersister($tokenStorage, $em))->supports(new class {});

        $this->assertFalse($actual);
    }

    public function testCommentHasAuthor()
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $comment = $this->getMockBuilder(Comment::class)
            ->setMethods(['getUser', 'setUser'])
            ->getMock();

        $comment->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue(new User()));

        $comment->expects($this->never())
            ->method('setUser');

        (new CommentDataPersister($tokenStorage, $em))->persist($comment);
    }

    public function testCommentHasNoUserAndIsNotAuthenticated()
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $comment = $this->getMockBuilder(Comment::class)
            ->setMethods(['setUser'])
            ->getMock();

        $comment->expects($this->never())
            ->method('setUser');

        (new CommentDataPersister($tokenStorage, $em))->persist($comment);
    }

    public function testAuthenticatedUserIsNotInstanceOfUserEntity()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->getMockForAbstractClass();
        $token = $this->getMockBuilder(TokenInterface::class)
            ->getMockForAbstractClass();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn('NonExisted class');

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $comment = $this->getMockBuilder(Comment::class)
            ->setMethods(['setUser'])
            ->getMock();

        $comment->expects($this->never())
            ->method('setUser');

        (new CommentDataPersister($tokenStorage, $em))->persist($comment);
    }

    public function testCommentHasNoUserAndIsAuthenticated()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->getMockForAbstractClass();
        $token = $this->getMockBuilder(TokenInterface::class)
            ->getMockForAbstractClass();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(new User());

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $comment = $this->getMockBuilder(Comment::class)
            ->setMethods(['setUser'])
            ->getMock();

        $comment->expects($this->once())
            ->method('setUser');

        (new CommentDataPersister($tokenStorage, $em))->persist($comment);
    }
}