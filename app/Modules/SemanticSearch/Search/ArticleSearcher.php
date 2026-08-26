<?php

namespace App\Modules\SemanticSearch\Search;

use App\Modules\SemanticSearch\Embeddings\ConceptEmbedder;
use App\Modules\SemanticSearch\Models\Article;

class ArticleSearcher
{
    private const RESULT_LIMIT = 5;

    public function __construct(private ConceptEmbedder $embedder) {}

    /** @return array<int, array{id: int, title: string}|array{id: int, title: string, score: float}> */
    public function search(string $query, string $mode): array
    {
        return $mode === 'semantic'
            ? $this->semantic($query)
            : $this->keyword($query);
    }

    /** @return array<int, array{id: int, title: string}> */
    public function all(): array
    {
        return Article::query()
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Article $article) => ['id' => $article->id, 'title' => $article->title])
            ->all();
    }

    /** @return array<int, array{id: int, title: string}> */
    private function keyword(string $query): array
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
    private function semantic(string $query): array
    {
        $queryVector = $this->embedder->embed($query);

        return Article::query()
            ->get(['id', 'title', 'embedding'])
            ->map(fn (Article $article) => [
                'id' => $article->id,
                'title' => $article->title,
                'score' => $this->embedder->cosineSimilarity($queryVector, $article->embedding),
            ])
            ->filter(fn (array $result) => $result['score'] > 0)
            ->sortByDesc('score')
            ->take(self::RESULT_LIMIT)
            ->values()
            ->all();
    }
}
