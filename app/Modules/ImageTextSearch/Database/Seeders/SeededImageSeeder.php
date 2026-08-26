<?php

namespace App\Modules\ImageTextSearch\Database\Seeders;

use App\Modules\ImageTextSearch\Embeddings\CrossModalEmbedder;
use App\Modules\ImageTextSearch\Models\SeededImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SeededImageSeeder extends Seeder
{
    /**
     * Bundled photo filenames (no extension) under resources/seed-images/ — the same
     * 16 real, freely-licensed JPEGs `image-similarity` ships, reused here as the
     * search corpus per the "reuse the storage/dataset shape, keep the flag
     * independent" precedent (see the module README).
     *
     * Each caption is hand-written in different words than the photo's own
     * filename/label — it stands in for what a real CLIP model would learn from an
     * (image, caption) training pair, since no such model runs here. It's what
     * `CrossModalEmbedder::embed()` actually sees for the image side; the pixels
     * themselves are only stored for display, never embedded.
     *
     * @var array<string, string>
     */
    private const CAPTIONS = [
        'forest-river' => 'sunlight filters through green woodland trees beside calm flowing water',
        'forest-path' => 'a quiet dirt trail winds between tall trees past brown fallen leaves',
        'lake-forest' => 'still blue water reflects the surrounding green trees at the shoreline',
        'standing-stones' => 'ancient grey stones rise from a misty green woodland clearing',
        'sea-wake' => 'a boat carves a white trail across cool blue ocean water',
        'city-bridge' => 'steel cables span a cool grey river beneath a cloudy sky',
        'snow-peaks' => 'jagged white mountain peaks rise above a cold blue sky',
        'misty-pines' => 'tall pine trees fade into cool grey mist and mountain air',
        'sunset-field' => 'warm amber light glows low across an open grassy field',
        'golden-hills' => 'rolling hills glow amber and warm beneath a low evening sun',
        'golden-hiker' => 'a lone hiker walks warm glowing hills at dusk',
        'orchard-sunset' => 'rows of fruit trees glow warm amber under a low evening sun',
        'leopard-road' => 'a spotted wild cat crosses a dusty brown farm road',
        'highland-cow' => 'a shaggy brown farm animal grazes on a rustic hillside',
        'black-puppy' => 'a small dark furry animal sits on a rustic wooden porch',
        'rustic-shed' => 'a weathered wooden barn stands brown and rustic in a farm field',
    ];

    public function run(): void
    {
        $embedder = new CrossModalEmbedder;

        foreach (self::CAPTIONS as $filename => $caption) {
            $binary = file_get_contents(self::path($filename));

            SeededImage::create([
                'label' => Str::title(str_replace('-', ' ', $filename)),
                'image' => 'data:image/jpeg;base64,'.base64_encode($binary),
                'embedding' => $embedder->embed($caption),
            ]);
        }
    }

    public static function path(string $filename): string
    {
        return __DIR__.'/../../resources/seed-images/'.$filename.'.jpg';
    }
}
