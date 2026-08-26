<?php

namespace App\Modules\ImageSimilarity\Database\Seeders;

use App\Modules\ImageSimilarity\Embeddings\ImageEmbedder;
use App\Modules\ImageSimilarity\Models\SeededImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SeededImageSeeder extends Seeder
{
    /**
     * Bundled photo filenames (no extension) under resources/seed-images/ — real,
     * freely-licensed JPEGs, see the module README for sourcing and licensing.
     *
     * @var array<int, string>
     */
    private const FILENAMES = [
        'forest-river', 'forest-path', 'lake-forest', 'standing-stones',
        'sea-wake', 'city-bridge', 'snow-peaks', 'misty-pines',
        'sunset-field', 'golden-hills', 'golden-hiker', 'orchard-sunset',
        'leopard-road', 'highland-cow', 'black-puppy', 'rustic-shed',
    ];

    public function run(): void
    {
        $embedder = new ImageEmbedder;

        foreach (self::FILENAMES as $filename) {
            $binary = file_get_contents(self::path($filename));

            SeededImage::create([
                'label' => Str::title(str_replace('-', ' ', $filename)),
                'image' => 'data:image/jpeg;base64,'.base64_encode($binary),
                'embedding' => $embedder->embed($binary),
            ]);
        }
    }

    public static function path(string $filename): string
    {
        return __DIR__.'/../../resources/seed-images/'.$filename.'.jpg';
    }
}
