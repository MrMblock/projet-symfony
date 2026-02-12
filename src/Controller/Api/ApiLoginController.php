<?php

namespace App\Controller\Api;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Missing credentials'], 401);
        }

        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $jwtManager->create($user),
        ]);
    }
}
