<?php

namespace Statamic\Addons\Redirects;

use Illuminate\Contracts\Http\Kernel;
use Statamic\Addons\Redirects\Middleware\RedirectsMiddleware;
use Statamic\Extend\ServiceProvider;

class RedirectsServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $storagePath = site_storage_path('addons/redirects/');

        $this->app->singleton(ManualRedirectsManager::class, function ($app) use ($storagePath) {
           return new ManualRedirectsManager($storagePath . 'manual.yaml', $app[RedirectsLogger::class]);
        });

        $this->app->singleton(AutoRedirectsManager::class, function ($app) use ($storagePath) {
            return new AutoRedirectsManager($storagePath . 'auto.yaml', $app[RedirectsLogger::class]);
        });

        $this->app->singleton(RedirectsProcessor::class, function ($app) {
            return new RedirectsProcessor(
                $app[ManualRedirectsManager::class],
                $app[AutoRedirectsManager::class],
                $app[RedirectsLogger::class],
                $this->getConfig()
            );
        });

        $this->app->singleton(RedirectsLogger::class, function () use ($storagePath) {
            return new RedirectsLogger($storagePath);
        });

        $this->app->singleton(RedirectsMaintenance::class, function () {
            return new RedirectsMaintenance();
        });

        $this->app->singleton(RedirectsAccessChecker::class, function () {
            return new RedirectsAccessChecker();
        });
    }

    public function boot()
    {
        $this->registerMiddleware();
    }

    public function provides()
    {
        return [
            ManualRedirectsManager::class,
            AutoRedirectsManager::class,
            RedirectsProcessor::class,
            RedirectsLogger::class,
            RedirectsMaintenance::class,
            RedirectsAccessChecker::class,
        ];
    }

    private function registerMiddleware()
    {
        $this->app[Kernel::class]->pushMiddleware(RedirectsMiddleware::class);
    }
}
