<?php

declare(strict_types=1);

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GoogleAuthController extends AbstractController
{
    public function __construct(
        private readonly GoogleClient $googleClient,
    ) {
    }

    #[Route('/google/connect', name: 'google_auth_connect', methods: [Request::METHOD_GET])]
    public function connect(): Response
    {
        return $this->googleClient->redirect(
            scopes: [
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
                'openid',
            ],
            options: [
                'access_type' => 'offline',
            ]);
    }

    #[Route('/google/callback', name: 'connect_google_check', methods: [Request::METHOD_GET])]
    public function callback(): Response
    {
        return $this->redirect('/login');
    }
}
