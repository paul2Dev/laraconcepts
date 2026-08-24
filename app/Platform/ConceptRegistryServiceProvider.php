<?php

namespace App\Platform;

use Illuminate\Support\ServiceProvider;

class ConceptRegistryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConceptRegistry::class);
    }
}
