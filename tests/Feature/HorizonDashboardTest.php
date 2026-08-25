<?php

use App\Modules\HorizonDashboard\HorizonDashboardServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

it('redirects into Horizon\'s own dashboard when the flag is on', function () {
    Feature::activate(HorizonDashboardServiceProvider::SLUG);

    $response = $this->get(route('horizon-dashboard.demo'));

    $response->assertRedirect(route('horizon.index'));
});

it('refuses to reach Horizon when the flag is off', function () {
    Feature::deactivate(HorizonDashboardServiceProvider::SLUG);

    $response = $this->get(route('horizon-dashboard.demo'));

    $response->assertStatus(503);
    $response->assertJson(['message' => 'unavailable']);
});

it('appears on the dashboard grouped under DevOps / Observability', function () {
    $response = $this->get(route('concepts.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['DevOps / Observability', 'Horizon Dashboard']);
});
