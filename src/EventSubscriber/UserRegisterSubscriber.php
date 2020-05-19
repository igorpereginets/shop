<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Service\MailerService;
use App\Utils\MDTokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class UserRegisterSubscriber implements EventSubscriberInterface
{
    const REGISTER_ROUTE = 'api_users_post_collection';
    /**
     * @var MDTokenGenerator
     */
    private $tokenGenerator;
    /**
     * @var MailerService
     */
    private $mailer;

    public function __construct(MDTokenGenerator $tokenGenerator, MailerService $mailer)
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }

    /**
     * @param ViewEvent|RequestEvent $event
     */
    public function onKernelView(RequestEvent $event)
    {
        $entity = $event->getControllerResult();

        if ($event->getRequest()->get('_route') !== self::REGISTER_ROUTE || !$entity instanceof User) {
            return;
        }

        $token = $this->tokenGenerator->generate();

        $entity->setConfirmationToken($token);
        $this->mailer->sendConfirmationToken($entity->getEmail(), $token);
    }

    public static function getSubscribedEvents()
    {
        return [
            ViewEvent::class => ['onKernelView', EventPriorities::POST_VALIDATE],
        ];
    }
}
