<?php

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminContextBuilder implements SerializerContextBuilderInterface
{
    /**
     * @var SerializerContextBuilderInterface
     */
    private $decorated;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(SerializerContextBuilderInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (isset($context['groups']) && $this->authorizationChecker->isGranted('ROLE_ADMIN') && true === $normalization) {
            $context['groups'][] = 'admin:get';
        }

        return $context;
    }
}