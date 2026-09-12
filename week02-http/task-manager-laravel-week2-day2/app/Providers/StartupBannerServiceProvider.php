<?php

namespace App\Providers;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class StartupBannerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $greetingService = $this->app->make(GreetingServiceInterface::class);

        Log::info('StartupBannerServiceProvider::boot() ran successfully.', [
            'resolved_greeting_implementation' => get_class($greetingService),
            'sample_output' => $greetingService->greet('Laravel'),
        ]);
    }
}
