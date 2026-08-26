<?php

namespace App\Modules\SemanticSearch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SemanticSearch\Database\Seeders\ArticleSeeder;
use App\Modules\SemanticSearch\Models\Article;
use App\Modules\SemanticSearch\Search\ArticleSearcher;
use App\Modules\SemanticSearch\SemanticSearchServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Laravel\Pennant\Feature;

class SemanticSearchDemoController extends Controller
{
    // Proves the concept on the dashboard's plain demo link, no query string needed — see README.
    private const DEFAULT_QUERY = 'notebook';

    public function show(Request $request, ArticleSearcher $searcher): Response|JsonResponse
    {
        $active = Feature::active(SemanticSearchServiceProvider::SLUG);

        if (! $active) {
            return $this->respond($request, active: false, query: '', mode: 'semantic', results: []);
        }

        $validated = $request->validate([
            'query' => ['sometimes', 'string'],
            'mode' => ['sometimes', Rule::in(['keyword', 'semantic'])],
        ]);
        if (Article::query()->doesntExist()) {
            (new ArticleSeeder)->run();
        }

        if (! $request->wantsJson() && ! $request->filled('query')) {
            return $this->respond($request, active: true, query: '', mode: $validated['mode'] ?? 'semantic', results: $searcher->all());
        }

        $query = $validated['query'] ?? self::DEFAULT_QUERY;
        $mode = $validated['mode'] ?? 'semantic';
        $results = $searcher->search($query, $mode);

        return $this->respond($request, active: true, query: $query, mode: $mode, results: $results);
    }

    private function respond(Request $request, bool $active, string $query, string $mode, array $results): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $active
                ? response()->json(['query' => $query, 'mode' => $mode, 'results' => $results])
                : response()->json(['message' => 'unavailable'], 503);
        }

        return response()
            ->view('semantic-search::demo', compact('active', 'query', 'mode', 'results'))
            ->setStatusCode($active ? 200 : 503);
    }
}
