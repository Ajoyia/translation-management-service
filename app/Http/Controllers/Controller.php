<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Translation Management Service API',
    description: 'API for managing translations across multiple locales with token-based authentication',
    contact: new OA\Contact(email: 'support@example.com')
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'Local Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Tag(name: 'Authentication', description: 'API Endpoints for authentication')]
#[OA\Tag(name: 'Translations', description: 'API Endpoints for translation management')]
#[OA\Tag(name: 'Export', description: 'API Endpoints for exporting translations')]
abstract class Controller
{
}
