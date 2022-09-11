<?php

namespace App\Providers;

use App\Contracts\IScoreEmployee;
use App\Service\ScoreEmployeeService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array|string[]
     */
    public array $singletons = [
        IScoreEmployee::class => ScoreEmployeeService::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
