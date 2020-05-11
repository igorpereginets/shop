<?php

namespace App\Controller\Action;

use App\Entity\ResetPassword;
use App\Service\ResetPasswordHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResetPasswordAction extends AbstractController
{
    public function __invoke(ResetPassword $data, ResetPasswordHandler $handler)
    {
        return new JsonResponse($handler->handle($data));
    }
}