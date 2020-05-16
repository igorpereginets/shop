<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\DTO\ResetPassword;
use App\Entity\DTO\ResetPasswordRequest;
use App\Service\ResetPasswordHandler;
use App\Service\ResetPasswordRequestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordRequestController extends AbstractController
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function request(ResetPasswordRequest $data, ResetPasswordRequestHandler $handler)
    {
        $this->validator->validate($data);
        $handler->handleRequest($data->getEmail());

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function reset(ResetPassword $data, ResetPasswordHandler $handler)
    {
        $this->validator->validate($data);
        $handler->handleReset($data);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}