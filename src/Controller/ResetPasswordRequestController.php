<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\DTO\ResetPassword;
use App\Entity\DTO\ResetPasswordRequest;
use App\Service\ResetPasswordRequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordRequestController extends AbstractController
{
    /**
     * @var ResetPasswordRequestHandler
     */
    private $handler;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ResetPasswordRequestHandler $handler, ValidatorInterface $validator)
    {
        $this->handler = $handler;
        $this->validator = $validator;
    }

    public function request(ResetPasswordRequest $data)
    {
        $this->validator->validate($data);
        $this->handler->handleRequest($data);

        return new Response('', 204);
    }

    public function reset(ResetPassword $data)
    {
        $this->validator->validate($data);
        $this->handler->handleReset($data);

        return new Response('', 204);
    }
}