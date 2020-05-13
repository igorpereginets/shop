<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\DTO\UserConfirmation;
use App\Service\ConfirmationService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserConfirmationController
{
    public function __invoke(UserConfirmation $data, ConfirmationService $confirmationService, ValidatorInterface $validator)
    {
        $validator->validate($data);

        try {
            $confirmationService->confirmUser($data);
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException;
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}