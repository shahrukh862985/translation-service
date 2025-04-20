<?php

namespace App\Providers;

use App\Repositories\LanguageRepository;
use App\Repositories\TagRepository;
use App\Repositories\TranslationRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TranslationRepository::class, function ($app) {
            return new TranslationRepository($app->make('App\Models\Translation'));
        });

        $this->app->singleton(TagRepository::class, function ($app) {
            return new TagRepository($app->make('App\Models\Tag'));
        });
        $this->app->singleton(LanguageRepository::class, function ($app) {
            return new LanguageRepository($app->make('App\Models\Language'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
