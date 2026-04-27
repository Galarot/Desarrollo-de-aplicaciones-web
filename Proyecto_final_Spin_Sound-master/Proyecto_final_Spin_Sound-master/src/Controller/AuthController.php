<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route(path: '/auth/google', name: 'app_auth_google', methods: ['GET'])]
    public function google(Request $request): Response
    {
        $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? null;
        $redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? null;

        if (!$clientId || !$redirectUri) {
            $this->addFlash('error', 'Google OAuth is not configured. Set GOOGLE_CLIENT_ID and GOOGLE_REDIRECT_URI in your environment.');
            return $this->redirectToRoute('app_login');
        }

        $state = bin2hex(random_bytes(16));
        $request->getSession()->set('oauth2_state', $state);

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'select_account',
        ]);

        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;

        return new RedirectResponse($url);
    }
}

