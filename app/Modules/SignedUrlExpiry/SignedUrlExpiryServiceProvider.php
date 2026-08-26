<?php

namespace App\Modules\SignedUrlExpiry;

use App\Modules\SignedUrlExpiry\Http\Controllers\SignedUrlExpiryController;
use App\Platform\ConceptRegistration;
use App\Platform\ConceptRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SignedUrlExpiryServiceProvider extends ServiceProvider
{
    public const SLUG = 'signed-url-expiry';

    public function boot(ConceptRegistry $registry): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'signed-url-expiry');

        $registry->register(new ConceptRegistration(
            slug: self::SLUG,
            name: 'Signed URL Expiry',
            description: 'Generates a short-lived signed download link with a live countdown; the download route rejects it once expired or if the signature is tampered with.',
            category: 'Performance & Security',
            demoRoute: 'signed-url-expiry.demo',
        ));

        Route::get('/concepts/signed-url-expiry/demo', [SignedUrlExpiryController::class, 'show'])
            ->name('signed-url-expiry.demo');

        Route::get('/concepts/signed-url-expiry/download', [SignedUrlExpiryController::class, 'download'])
            ->name('signed-url-expiry.download');
    }
}
