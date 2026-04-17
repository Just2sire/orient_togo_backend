<?php

namespace App\Providers;

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
