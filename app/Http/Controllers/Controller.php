<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="OrientTogo API",
 *     description="Documentation complète de l'API OrientTogo",
 *
 *     @OA\Contact(
 *         email="support@orienttogo.tg",
 *         name="Support OrientTogo"
 *     ),
 *
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Serveur de développement"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Authentification via Laravel Sanctum. Entrez le token Bearer."
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Une erreur est survenue.")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Les données fournies sont invalides."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="field_name",
 *             type="array",
 *
 *             @OA\Items(type="string", example="Le champ est obligatoire.")
 *         )
 *     )
 * )
 */
abstract class Controller
{
    //
}
