<?php

namespace App\Modules\HorizonDashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HorizonDashboard\HorizonDashboardServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Pennant\Feature;

class HorizonDashboardController extends Controller
{
    public function show(): RedirectResponse|JsonResponse
    {
        if (! Feature::active(HorizonDashboardServiceProvider::SLUG)) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        return redirect()->route('horizon.index');
    }
}
