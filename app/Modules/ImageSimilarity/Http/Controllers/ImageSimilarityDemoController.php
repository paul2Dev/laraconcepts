<?php

namespace App\Modules\ImageSimilarity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ImageSimilarity\Database\Seeders\SeededImageSeeder;
use App\Modules\ImageSimilarity\Embeddings\ImageEmbedder;
use App\Modules\ImageSimilarity\ImageSimilarityServiceProvider;
use App\Modules\ImageSimilarity\Models\SeededImage;
use App\Modules\ImageSimilarity\Search\ImageSearcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Pennant\Feature;

class ImageSimilarityDemoController extends Controller
{
    public function show(): Response
    {
        $active = $this->isActive();

        if ($active) {
            $this->ensureSeeded();
        }

        $seeded = $active
            ? SeededImage::query()->orderBy('label')->get(['id', 'label', 'image'])
            : collect();

        return response()
            ->view('image-similarity::demo', ['active' => $active, 'seeded' => $seeded])
            ->setStatusCode($active ? 200 : 503);
    }

    public function upload(Request $request, ImageEmbedder $embedder, ImageSearcher $searcher): JsonResponse
    {
        if (! $this->isActive()) {
            return response()->json(['message' => 'unavailable'], 503);
        }

        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $this->ensureSeeded();

        $vector = $embedder->embed($request->file('image')->get());

        return response()->json(['results' => $searcher->nearest($vector)]);
    }

    private function isActive(): bool
    {
        return Feature::active(ImageSimilarityServiceProvider::SLUG);
    }

    private function ensureSeeded(): void
    {
        if (SeededImage::query()->doesntExist()) {
            (new SeededImageSeeder)->run();
        }
    }
}
