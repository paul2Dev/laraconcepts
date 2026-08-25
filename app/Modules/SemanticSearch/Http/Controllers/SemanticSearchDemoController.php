<?php

namespace App\Modules\SemanticSearch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SemanticSearch\Database\Seeders\ArticleSeeder;
use App\Modules\SemanticSearch\Embeddings\ConceptEmbedder;
use App\Modules\SemanticSearch\Models\Article;
use App\Modules\SemanticSearch\SemanticSearchServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Pennant\Feature;

class SemanticSearchDemoController extends Controller
{
    private const RESULT_LIMIT = 5;

    // Proves the concept on the dashboard's plain demo link, no query string needed — see README.
    private const DEFAULT_QUERY = 'notebook';

    public function show(Request $request, ConceptEmbedder $embedder): JsonResponse
    {
        if (! Feature::active(SemanticSearchServiceProvider::SLUG)) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $validated = $request->validate([
            'query' => ['sometimes', 'string'],
            'mode' => ['sometimes', Rule::in(['keyword', 'semantic'])],
        ]);
        $query = $validated['query'] ?? self::DEFAULT_QUERY;
        $mode = $validated['mode'] ?? 'semantic';
        if (Article::query()->doesntExist()) {
            (new ArticleSeeder)->run();
        }

        $results = $mode === 'semantic'
            ? $this->semanticSearch($query, $embedder)
            : $this->keywordSearch($query);

        return response()->json([
            'query' => $query,
            'mode' => $mode,
            'results' => $results,
        ]);
    }

    /** @return array<int, array{id: int, title: string}> */
    private function keywordSearch(string $query): array
    {
        return Article::query()
            ->where('title', 'like', "%{$query}%")
            ->orWhere('body', 'like', "%{$query}%")
            ->limit(self::RESULT_LIMIT)
            ->get(['id', 'title'])
            ->map(fn (Article $article) => ['id' => $article->id, 'title' => $article->title])
            ->all();
    }

    /** @return array<int, array{id: int, title: string, score: float}> */
    private function semanticSearch(string $query, ConceptEmbedder $embedder): array
    {
        $queryVector = $embedder->embed($query);

        return Article::query()
            ->get(['id', 'title', 'embedding'])
            ->map(fn (Article $article) => [
                'id' => $article->id,
                'title' => $article->title,
                'score' => $embedder->cosineSimilarity($queryVector, $article->embedding),
            ])
            ->filter(fn (array $result) => $result['score'] > 0)
            ->sortByDesc('score')
            ->take(self::RESULT_LIMIT)
            ->values()
            ->all();
    }
}
