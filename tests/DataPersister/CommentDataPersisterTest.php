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
    public function providerCommentHasNoUser(): array
    {
        return [
            'authenticated_user_creates_comment' => [true, true, new User()],
            'user_was_not_authenticated' => [false, false, null],
            'user_authenticated_as_anonymous_user' => [true, false, 'anon']
        ];
    }

    public function testCommentHasUser()
    {
        $comment = $this->getComment(true, false);

        $actualComment = (new CommentDataPersister($this->getTokenStorage(), $this->getEntityManager()))
            ->persist($comment);

        $this->assertSame($comment, $actualComment);
    }

    /**
     * @dataProvider providerCommentHasNoUser
     */
    public function testCommentHasNoUser(bool $shouldGetUserMethodBeCalled, bool $shouldSetUserMethodBeCalled, $returnByToken)
    {
        $comment = $this->getComment(false, $shouldSetUserMethodBeCalled);

        $token = $this->getToken($shouldGetUserMethodBeCalled, $returnByToken);
        $tokenStorage = $this->getTokenStorage($shouldGetUserMethodBeCalled ? $token : null);

        $actualComment = (new CommentDataPersister($tokenStorage, $this->getEntityManager()))->persist($comment);

        $this->assertSame($comment, $actualComment);
    }

    private function getEntityManager()
    {
        $em = $this->getMockForAbstractClass(EntityManagerInterface::class);

        $em->expects($this->once())
            ->method('persist')
            ->withAnyParameters();

        return $em;
    }

    private function getToken(bool $shouldGetUserMethodBeCalled, $return)
    {
        $token = $this->getMockBuilder(TokenInterface::class)
            ->getMockForAbstractClass();

        $token->expects($shouldGetUserMethodBeCalled ? $this->once() : $this->never())
            ->method('getUser')
            ->willReturn($return);

        return $token;
    }

    private function getTokenStorage($return = null)
    {
        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->getMockForAbstractClass();

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($return);

        return $tokenStorage;
    }

    private function getComment(bool $hasUser, bool $shouldSetUserMethodBeCalled)
    {
        $comment = $this->getMockBuilder(Comment::class)
            ->setMethods(['setUser', 'getUser'])
            ->getMock();

        $comment->expects($this->once())
            ->method('getUser')
            ->willReturn($hasUser ? new User() : null);

        $comment->expects($shouldSetUserMethodBeCalled ? $this->once() : $this->never())
            ->method('setUser')
            ->withAnyParameters();

        return $comment;
    }
}