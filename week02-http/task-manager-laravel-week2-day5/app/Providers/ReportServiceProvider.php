<?php

namespace App\Providers;

use App\Services\Contracts\ReportGeneratorInterface;
use App\Services\ReportGeneratorService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        file_put_contents(
            storage_path('logs/deferred-provider-proof.log'),
            'ReportServiceProvider::register() ran at '.now()->toDateTimeString().PHP_EOL,
            FILE_APPEND,
        );

        $this->app->singleton(
            ReportGeneratorInterface::class,
            ReportGeneratorService::class,
        );
    }

    public function provides(): array
    {
        return [
            ReportGeneratorInterface::class,
        ];
    }
}
