<?php

namespace App\Services;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseService
{
    use ApiResponse;

    public function __construct(
        private readonly CourseRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des courses (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, CourseResource::class);
        }, 'Impossible de récupérer les courses.');
    }

    /**
     * Affiche un(e) Course.
     */
    public function show(Course $course): JsonResponse
    {
        return $this->try(function () use ($course) {
            $model = $this->repository->findOrFail($course->id);
            $model->load('establishment', 'careers');

            return $this->success(new CourseResource($model), 'Course récupéré.');
        }, 'Impossible de récupérer ce course.');
    }

    /**
     * Crée un(e) Course.
     */
    public function store(StoreCourseRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new CourseResource($model));
        }, 'Impossible de créer le course.');
    }

    /**
     * Met à jour un(e) Course.
     */
    public function update(Course $course, UpdateCourseRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $course) {
            $model = $this->repository->update($course, $request->validated());

            return $this->updated(new CourseResource($model));
        }, 'Impossible de mettre à jour le course.');
    }

    /**
     * Supprime un(e) Course.
     */
    public function destroy(Course $course): JsonResponse
    {
        return $this->try(function () use ($course) {
            $this->repository->delete($course);

            return $this->deleted();
        }, 'Impossible de supprimer le course.');
    }
}
