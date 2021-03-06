<?php

namespace Devsrv\ScheduledAction;

use Illuminate\Support\ServiceProvider;
use Devsrv\ScheduledAction\Console\PollScheduledAction;

class ScheduledActionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if (! class_exists('CreateActionsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_actions_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_actions_table.php'),
                ], 'migrations');
            }

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('scheduled-action.php'),
            ], 'config');

            $this->commands([
                PollScheduledAction::class,
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'scheduled-action');
    }
}
