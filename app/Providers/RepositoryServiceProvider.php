<?php

namespace App\Providers;

use App\Repositories\CareerRepository;
use App\Repositories\Contracts\CareerRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\EstablishmentRepositoryInterface;
use App\Repositories\Contracts\FieldRepositoryInterface;
use App\Repositories\Contracts\GrowthSectorRepositoryInterface;
use App\Repositories\Contracts\OtpCodeRepositoryInterface;
use App\Repositories\Contracts\SectorDataRepositoryInterface;
use App\Repositories\Contracts\SerieRepositoryInterface;
use App\Repositories\Contracts\SubjectCoefficientRepositoryInterface;
use App\Repositories\Contracts\TagRepositoryInterface;
use App\Repositories\Contracts\UserDeviceRepositoryInterface;
use App\Repositories\Contracts\UserFavoriteRepositoryInterface;
use App\Repositories\Contracts\UserProfileRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\CourseRepository;
use App\Repositories\EstablishmentRepository;
use App\Repositories\FieldRepository;
use App\Repositories\GrowthSectorRepository;
use App\Repositories\OtpCodeRepository;
use App\Repositories\SectorDataRepository;
use App\Repositories\SerieRepository;
use App\Repositories\SubjectCoefficientRepository;
use App\Repositories\TagRepository;
use App\Repositories\UserDeviceRepository;
use App\Repositories\UserFavoriteRepository;
use App\Repositories\UserProfileRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TagRepositoryInterface::class,
            TagRepository::class,
        );
        $this->app->bind(
            FieldRepositoryInterface::class,
            FieldRepository::class,
        );
        $this->app->bind(
            SectorDataRepositoryInterface::class,
            SectorDataRepository::class,
        );
        $this->app->bind(
            GrowthSectorRepositoryInterface::class,
            GrowthSectorRepository::class,
        );
        $this->app->bind(
            CareerRepositoryInterface::class,
            CareerRepository::class,
        );
        $this->app->bind(
            CourseRepositoryInterface::class,
            CourseRepository::class,
        );
        $this->app->bind(
            EstablishmentRepositoryInterface::class,
            EstablishmentRepository::class,
        );
        $this->app->bind(
            SubjectCoefficientRepositoryInterface::class,
            SubjectCoefficientRepository::class,
        );
        $this->app->bind(
            SerieRepositoryInterface::class,
            SerieRepository::class,
        );
        $this->app->bind(
            UserFavoriteRepositoryInterface::class,
            UserFavoriteRepository::class,
        );
        $this->app->bind(
            UserDeviceRepositoryInterface::class,
            UserDeviceRepository::class,
        );
        $this->app->bind(
            UserProfileRepositoryInterface::class,
            UserProfileRepository::class,
        );
        $this->app->bind(
            OtpCodeRepositoryInterface::class,
            OtpCodeRepository::class,
        );
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class,
        );
    }

    public function boot(): void
    {
        //
    }
}
