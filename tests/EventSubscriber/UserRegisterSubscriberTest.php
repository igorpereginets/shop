<?php

namespace App\Tests\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\EventSubscriber\UserRegisterSubscriber;
use App\Service\MailerService;
use App\Utils\MDTokenGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class UserRegisterSubscriberTest extends TestCase
{
    public function testIsNotUserRegistrationRoute()
    {
        $tokenGenerator = $this->createMock(MDTokenGenerator::class);
        $mailer = $this->createMock(MailerService::class);
        $request = $this->createMock(Request::class);
        $user = $this->createMock(User::class);
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequest', 'getControllerResult'])
            ->getMock();

        $request->method('get')
            ->with('_route')
            ->willReturn('NonExistingRoute');

        $event->method('getRequest')->willReturn($request);

        $event->method('getControllerResult')->willReturn($user);

        $user->expects($this->never())
            ->method('setConfirmationToken');

        (new UserRegisterSubscriber($tokenGenerator, $mailer))->onKernelView($event);
    }

    public function testEntityFromControllerIsNotUserInstance()
    {
        $tokenGenerator = $this->createMock(MDTokenGenerator::class);
        $mailer = $this->createMock(MailerService::class);
        $request = $this->createMock(Request::class);
        $object = $this->getMockBuilder('NonExistingClass')
            ->setMethods(['setConfirmationToken'])
            ->getMock();
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequest', 'getControllerResult'])
            ->getMock();

        $request->method('get')
            ->with('_route')
            ->willReturn(UserRegisterSubscriber::REGISTER_ROUTE);

        $event->method('getRequest')->willReturn($request);

        $event->method('getControllerResult')->willReturn($object);

        $object->expects($this->never())
            ->method('setConfirmationToken');

        (new UserRegisterSubscriber($tokenGenerator, $mailer))->onKernelView($event);
    }

    public function testSuccessRegistration()
    {
        $tokenGenerator = $this->createMock(MDTokenGenerator::class);
        $mailer = $this->createMock(MailerService::class);
        $request = $this->createMock(Request::class);
        $user = $this->createMock(User::class);
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequest', 'getControllerResult'])
            ->getMock();

        $event->method('getRequest')->willReturn($request);
        $event->method('getControllerResult')->willReturn($user);
        $user->method('getEmail')->willReturn('fakeEmail@test.com');
        $tokenGenerator->method('generate')->willReturn('fakeToken');

        $request->method('get')
            ->with('_route')
            ->willReturn(UserRegisterSubscriber::REGISTER_ROUTE);

        $user->expects($this->once())
            ->method('setConfirmationToken');

        (new UserRegisterSubscriber($tokenGenerator, $mailer))->onKernelView($event);
    }

    public function testProperSubscribedEvent()
    {
        $actual = UserRegisterSubscriber::getSubscribedEvents();

        $this->assertIsArray($actual);
        $this->assertArrayHasKey(ViewEvent::class, $actual);
        $this->assertCount(2, $actual[ViewEvent::class]);
        $this->assertIsString($actual[ViewEvent::class][0]);
        $this->assertEquals(EventPriorities::POST_VALIDATE, $actual[ViewEvent::class][1]);
    }
}