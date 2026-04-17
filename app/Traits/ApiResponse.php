<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
// use Illuminate\Http\JsonResponse as HttpJsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

trait ApiResponse
{
    // =========================================================================
    // ✅ SUCCÈS — 2xx
    // =========================================================================

    /**
     * Réponse générique de succès (200).
     *
     * @param  mixed  $data  Données à retourner (Resource, array, Model, etc.)
     * @param  string  $message  Message lisible
     * @param  int  $status  Code HTTP (défaut : 200)
     * @param  array  $meta  Métadonnées supplémentaires à fusionner dans la réponse
     */
    protected function success(
        mixed $data = null,
        string $message = 'Opération réussie.',
        int $status = 200,
        array $meta = []
    ): \Illuminate\Http\JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            // Les JsonResource / ResourceCollection sont sérialisés proprement
            $payload['data'] = $data instanceof JsonResource || $data instanceof ResourceCollection
                ? $data->resolve()
                : $data;
        }

        if (! empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * Ressource créée avec succès (201).
     */
    protected function created(
        mixed $data = null,
        string $message = 'Ressource créée avec succès.'
    ): \Illuminate\Http\JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * Ressource mise à jour avec succès (200).
     */
    protected function updated(
        mixed $data = null,
        string $message = 'Ressource mise à jour avec succès.'
    ): \Illuminate\Http\JsonResponse {
        return $this->success($data, $message, 200);
    }

    /**
     * Ressource supprimée — pas de corps (204).
     * Note : 204 n'a pas de corps HTTP. Utilise deleted() si tu veux un message JSON.
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Suppression avec message JSON (200).
     */
    protected function deleted(string $message = 'Ressource supprimée avec succès.'): JsonResponse
    {
        return $this->success(null, $message, 200);
    }

    /**
     * Réponse acceptée — traitement asynchrone en cours (202).
     */
    protected function accepted(string $message = 'Requête acceptée, traitement en cours.'): JsonResponse
    {
        return $this->success(null, $message, 202);
    }

    // =========================================================================
    // 📄 PAGINATION — Collection paginée avec métadonnées
    // =========================================================================

    /**
     * Collection paginée avec métadonnées de pagination.
     *
     * @param  string|null  $resourceClass  Classe Resource à appliquer sur les items
     */
    protected function paginated(
        LengthAwarePaginator $paginator,
        ?string $resourceClass = null,
        string $message = 'Données récupérées avec succès.'
    ): \Illuminate\Http\JsonResponse {
        $items = $resourceClass
            ? $resourceClass::collection($paginator->items())
            : $paginator->items();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'meta' => [
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ],
        ], 200);
    }

    /**
     * Collection simple (non paginée).
     */
    protected function collection(
        Collection|array $items,
        string $message = 'Données récupérées avec succès.',
        int $status = 200
    ): \Illuminate\Http\JsonResponse {
        return $this->success($items, $message, $status);
    }

    // =========================================================================
    // ❌ ERREURS — 4xx / 5xx
    // =========================================================================

    /**
     * Réponse d'erreur générique.
     *
     * @param  string  $message  Message d'erreur lisible
     * @param  int  $status  Code HTTP
     * @param  array|null  $errors  Détails des erreurs (validation, champs, etc.)
     * @param  mixed  $debug  Informations de debug (affichées uniquement si APP_DEBUG=true)
     */
    protected function error(
        string $message = 'Une erreur est survenue.',
        int $status = 400,
        ?array $errors = null,
        mixed $debug = null
    ): \Illuminate\Http\JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        if ($debug !== null && config('app.debug')) {
            $payload['debug'] = $debug;
        }

        return response()->json($payload, $status);
    }

    /**
     * Erreur de validation (422 Unprocessable Entity).
     *
     * @param  array  $errors  Tableau d'erreurs par champ (format Laravel Validator)
     */
    protected function validationError(
        array $errors = [],
        string $message = 'Les données fournies sont invalides.'
    ): \Illuminate\Http\JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    /**
     * Ressource introuvable (404 Not Found).
     */
    protected function notFound(
        string $message = 'Ressource introuvable.'
    ): \Illuminate\Http\JsonResponse {
        return $this->error($message, 404);
    }

    /**
     * Non authentifié (401 Unauthorized).
     */
    protected function unauthorized(
        string $message = 'Authentification requise.'
    ): \Illuminate\Http\JsonResponse {
        return $this->error($message, 401);
    }

    /**
     * Accès interdit — authentifié mais pas autorisé (403 Forbidden).
     */
    protected function forbidden(
        string $message = 'Vous n\'êtes pas autorisé à effectuer cette action.'
    ): \Illuminate\Http\JsonResponse {
        return $this->error($message, 403);
    }

    /**
     * Conflit — ressource déjà existante (409 Conflict).
     */
    protected function conflict(
        string $message = 'Un conflit est survenu avec la ressource existante.'
    ): \Illuminate\Http\JsonResponse {
        return $this->error($message, 409);
    }

    /**
     * Trop de requêtes — rate limiting (429 Too Many Requests).
     */
    protected function tooManyRequests(
        string $message = 'Trop de requêtes. Veuillez réessayer plus tard.'
    ): \Illuminate\Http\JsonResponse {
        return $this->error($message, 429);
    }

    /**
     * Erreur interne du serveur (500).
     *
     * @param  \Throwable|null  $exception  L'exception capturée (pour le debug)
     */
    protected function serverError(
        string $message = 'Erreur interne du serveur.',
        ?\Throwable $exception = null
    ): \Illuminate\Http\JsonResponse {
        $debug = null;

        if ($exception !== null && config('app.debug')) {
            $debug = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->take(5)->toArray(),
            ];
        }

        return $this->error($message, 500, null, $debug);
    }

    /**
     * Service indisponible (503 Service Unavailable).
     */
    protected function serviceUnavailable(
        string $message = 'Le service est temporairement indisponible.'
    ): \Illuminate\Http\JsonResponse {
        return $this->error($message, 503);
    }

    // =========================================================================
    // 🛠️ UTILITAIRES
    // =========================================================================

    /**
     * Réponse conditionnelle : succès ou erreur selon un booléen.
     *
     * Exemple : return $this->successOr($updated, 'Mis à jour.', 'Échec de la mise à jour.');
     *
     * @param  mixed  $data  Données à retourner en cas de succès
     */
    protected function successOr(
        bool $condition,
        string $successMessage = 'Opération réussie.',
        string $errorMessage = 'Opération échouée.',
        mixed $data = null
    ): \Illuminate\Http\JsonResponse {
        return $condition
            ? $this->success($data, $successMessage)
            : $this->error($errorMessage, 400);
    }

    /**
     * Wraps un callback dans un try/catch et retourne automatiquement serverError() en cas d'exception.
     *
     * Exemple :
     *   return $this->try(fn () => $this->success($service->create($data)), 'Création impossible.');
     *
     * @param  callable  $callback  Doit retourner un JsonResponse
     * @param  string  $errorMessage  Message en cas d'erreur inattendue
     */
    protected function try(
        callable $callback,
        ?string $errorMessage = null
    ): \Illuminate\Http\JsonResponse {
        try {
            return $callback();
        } catch (ModelNotFoundException) {
            return $this->notFound();
        } catch (AuthorizationException) {
            return $this->forbidden();
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        } catch (\Throwable $e) {
            return $this->serverError($errorMessage ?? $e->getMessage(), $e);
        }
    }
}
