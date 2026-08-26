<?php

use App\Modules\SignedUrlExpiry\SignedUrlExpiryServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('accepts a signed download link while the signature is still valid', function () {
    Feature::activate(SignedUrlExpiryServiceProvider::SLUG);

    $signedUrl = URL::temporarySignedRoute('signed-url-expiry.download', now()->addSeconds(30));

    $response = $this->get($signedUrl);

    $response->assertOk();
});

it('rejects the download once the signed link has expired', function () {
    Feature::activate(SignedUrlExpiryServiceProvider::SLUG);

    $signedUrl = URL::temporarySignedRoute('signed-url-expiry.download', now()->addSeconds(30));

    $this->travel(31)->seconds();

    $response = $this->get($signedUrl);

    $response->assertStatus(403);
});

it('rejects a download link whose signature was tampered with', function () {
    Feature::activate(SignedUrlExpiryServiceProvider::SLUG);

    $signedUrl = URL::temporarySignedRoute('signed-url-expiry.download', now()->addSeconds(30));

    $response = $this->get($signedUrl.'-tampered');

    $response->assertStatus(403);
});

it('refuses to generate a link when the flag is off', function () {
    Feature::deactivate(SignedUrlExpiryServiceProvider::SLUG);

    $response = $this->get(route('signed-url-expiry.demo'));

    $response->assertStatus(503);
});

it('renders the demo page with a signed link when the flag is on', function () {
    Feature::activate(SignedUrlExpiryServiceProvider::SLUG);

    $response = $this->get(route('signed-url-expiry.demo'));

    $response->assertOk();
});

it('appears on the dashboard grouped under Performance & Security', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Performance & Security', 'Signed URL Expiry']);
});
