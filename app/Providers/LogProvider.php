<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Log\Logger;
use App\Jobs;

class LogProvider extends ServiceProvider
{
    /**
     * Register log services.
     *
     * @return void
     */
    public function register()
    {
        $provider = function () {
            return Log::channel('failed-user-imports');
        };
        $this->app->when(Jobs\ProcessRecordsFile::class)
            ->needs(Logger::class)
            ->give($provider);
        $this->app->when(Jobs\StoreNewUser::class)
            ->needs(Logger::class)
            ->give($provider);
    }
}
