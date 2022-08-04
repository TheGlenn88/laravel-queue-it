<?php

namespace Theglenn88\LaravelQueueIt;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use TheGlenn88\LaravelQueueIt\Http\Middleware\QueueItMiddleware;

class QueueItServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/queueit.php' => config_path('queueit.php'),
            ], ['queue-it-config']);
        }

        $this->configureMiddleware();
        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
    }

    /**
     * Configure the Queue-It middleware and priority.
     *
     * @return void
     */
    protected function configureMiddleware()
    {
        $kernel = $this->app->make(Kernel::class);

        $kernel->prependMiddlewareToGroup('web', QueueItMiddleware::class);
    }
}
