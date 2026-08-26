<?php

namespace App\Modules\ImageSimilarity\Search;

use App\Modules\ImageSimilarity\Embeddings\ImageEmbedder;
use App\Modules\ImageSimilarity\Models\SeededImage;

class ImageSearcher
{
    private const RESULT_LIMIT = 5;

    public function __construct(private ImageEmbedder $embedder) {}

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
