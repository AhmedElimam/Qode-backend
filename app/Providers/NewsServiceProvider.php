<?php

namespace App\Providers;

use App\Repositories\ArticleRepository;
use App\Repositories\UserPreferenceRepository;
use App\Services\GuardianApiService;
use App\Services\MediaStackApiService;
use App\Services\NewsAggregationService;
use App\Services\NewYorkTimesApiService;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArticleRepository::class);
        $this->app->singleton(UserPreferenceRepository::class);
        
        $this->app->singleton(GuardianApiService::class, function ($app) {
            return new GuardianApiService(
                $app->make(ArticleRepository::class),
                config('services.guardian.key')
            );
        });
        
        $this->app->singleton(NewYorkTimesApiService::class, function ($app) {
            return new NewYorkTimesApiService(
                $app->make(ArticleRepository::class),
                config('services.nytimes.key')
            );
        });
        
        $this->app->singleton(MediaStackApiService::class, function ($app) {
            return new MediaStackApiService();
        });
        
        $this->app->singleton(NewsAggregationService::class, function ($app) {
            return new NewsAggregationService(
                $app->make(ArticleRepository::class),
                $app->make(GuardianApiService::class),
                $app->make(NewYorkTimesApiService::class),
                $app->make(MediaStackApiService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
