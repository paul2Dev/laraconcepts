<?php

namespace App\Modules\ImageTextSearch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ImageTextSearch\Database\Seeders\SeededImageSeeder;
use App\Modules\ImageTextSearch\Embeddings\CrossModalEmbedder;
use App\Modules\ImageTextSearch\ImageTextSearchServiceProvider;
use App\Modules\ImageTextSearch\Models\SeededImage;
use App\Modules\ImageTextSearch\Search\ImageTextSearcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Pennant\Feature;

class ImageTextSearchDemoController extends Controller
{
    // Proves the concept on the dashboard's plain demo link, no query string needed — see README.
    private const DEFAULT_QUERY = 'golden hour over a field';

    public function show(Request $request, CrossModalEmbedder $embedder, ImageTextSearcher $searcher): Response|JsonResponse
    {
        $active = Feature::active(ImageTextSearchServiceProvider::SLUG);

        if (! $active) {
            return $this->respond($request, active: false, query: '', results: []);
        }

        $validated = $request->validate([
            'query' => ['sometimes', 'string'],
        ]);

        $this->ensureSeeded();

        // No query yet: show the full seeded set on first page load, but a direct
        // JSON hit (e.g. curl) still gets a proof-of-concept default, same as
        // semantic-search's `notebook` default.
        $query = $request->filled('query')
            ? $validated['query']
            : ($request->wantsJson() ? self::DEFAULT_QUERY : '');

        $results = $query === ''
            ? $searcher->all()
            : $searcher->nearest($embedder->embed($query));

        return $this->respond($request, active: true, query: $query, results: $results);
    }

    private function respond(Request $request, bool $active, string $query, array $results): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $active
                ? response()->json(['query' => $query, 'results' => $results])
                : response()->json(['message' => 'unavailable'], 503);
        }

        return response()
            ->view('image-text-search::demo', compact('active', 'query', 'results'))
            ->setStatusCode($active ? 200 : 503);
    }

    private function ensureSeeded(): void
    {
        if (SeededImage::query()->doesntExist()) {
            (new SeededImageSeeder)->run();
        }
    }
}
