<?php

namespace App\Modules\ImageTextSearch\Search;

use App\Modules\ImageTextSearch\Embeddings\CrossModalEmbedder;
use App\Modules\ImageTextSearch\Models\SeededImage;

class ImageTextSearcher
{
    private const RESULT_LIMIT = 5;

    public function __construct(private CrossModalEmbedder $embedder) {}

    /** @return array<int, array{id: int, label: string, image: string}> */
    public function all(): array
    {
        return SeededImage::query()
            ->orderBy('label')
            ->get(['id', 'label', 'image'])
            ->map(fn (SeededImage $seed) => [
                'id' => $seed->id,
                'label' => $seed->label,
                'image' => $seed->image,
            ])
            ->all();
    }

    /**
     * @param  array<int, float>  $queryVector
     * @return array<int, array{id: int, label: string, image: string, distance: float}>
     */
    public function nearest(array $queryVector): array
    {
        return SeededImage::query()
            ->get(['id', 'label', 'image', 'embedding'])
            ->map(fn (SeededImage $seed) => [
                'id' => $seed->id,
                'label' => $seed->label,
                'image' => $seed->image,
                'distance' => $this->embedder->distance($queryVector, $seed->embedding),
            ])
            ->sortBy('distance')
            ->take(self::RESULT_LIMIT)
            ->values()
            ->all();
    }
}
