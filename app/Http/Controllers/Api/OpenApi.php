<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'OrientTogo API',
    description: "Documentation officielle de l'API OrientTogo (Laravel 13)"
)]
#[OA\Contact(
    email: 'contact@orienttogo.tg',
    name: 'Équipe Technique OrientTogo'
)]
#[OA\Server(
    url: '/api',
    description: 'Serveur Local / Base Path'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Utilisez le token retourné lors de la connexion.'
)]
class OpenApi {}
