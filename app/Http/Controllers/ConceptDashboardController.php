<?php

namespace App\Http\Controllers;

use App\Platform\ConceptRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laravel\Pennant\Feature;

class ConceptDashboardController extends Controller
{
    public function index(ConceptRegistry $registry): View
    {
        $groups = $registry->groupedByCategory()->map(
            fn ($concepts) => $concepts->map(fn ($concept) => [
                'concept' => $concept,
                'active' => Feature::active($concept->slug),
            ])
        );

        return view('concepts.dashboard', ['groups' => $groups]);
    }

    public function toggle(string $concept, ConceptRegistry $registry): RedirectResponse
    {
        abort_unless($registry->has($concept), 404);

        Feature::active($concept) ? Feature::deactivate($concept) : Feature::activate($concept);

        return redirect()->route('concepts.dashboard');
    }
}
