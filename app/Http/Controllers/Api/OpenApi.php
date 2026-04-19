<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'OrientTogo API',
    description: "Documentation officielle de l'API OrientTogo (Laravel 13). 
    Toutes les requêtes doivent être préfixées par `/api/v1`. 
    L'authentification se fait via Laravel Sanctum (Bearer Token)."
)]
#[OA\Contact(
    email: 'contact@orienttogo.tg',
    name: 'Équipe Technique OrientTogo'
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Serveur de développement'
)]
#[OA\Server(
    url: '/api',
    description: 'Serveur Local (Base Path)'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Utilisez le token retourné lors de la connexion (login ou verify OTP).'
)]

#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Une erreur est survenue.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Les données fournies sont invalides.'),
        new OA\Property(
            property: 'errors',
            properties: [
                new OA\Property(
                    property: 'field_name',
                    type: 'array',
                    items: new OA\Items(type: 'string', example: 'Le champ est obligatoire.')
                ),
            ],
            type: 'object'
        ),
    ],
    type: 'object'
)]
class OpenApi {}
