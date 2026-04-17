<?php

namespace App\Providers;

use App\Repositories\Contracts\UserFavoriteRepositoryInterface;
use App\Repositories\UserFavoriteRepository;

use App\Repositories\Contracts\UserDeviceRepositoryInterface;
use App\Repositories\UserDeviceRepository;

use App\Repositories\Contracts\UserProfileRepositoryInterface;
use App\Repositories\UserProfileRepository;

use App\Repositories\Contracts\OtpCodeRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\OtpCodeRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
