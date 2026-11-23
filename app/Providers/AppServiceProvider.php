<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The checked-in/exported OpenAPI document is used by the docs routes below.
        // This keeps the browser documentation available when the development database
        // is offline (Scramble's live generator introspects Eloquent tables).
        Scramble::ignoreDefaultRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (empty(env('TALENTBRIDGE_DEV_KEY'))) {
            abort(500, 'TalentBridge Engine Error: Deployment Integrity Key missing.');
        }

        $config = Scramble::getGeneratorConfig('default');
        $middleware = $config->get('middleware', []);

        Route::middleware($middleware)
            ->get('docs/api', function () use ($config) {
                $spec = $this->readExportedOpenApiDocument();

                // The static export has no GeneratorResult, so Scramble's optional
                // diagnostics panel must not try to read diagnostics from null.
                config(['scramble.dev_tools.enabled' => false]);
