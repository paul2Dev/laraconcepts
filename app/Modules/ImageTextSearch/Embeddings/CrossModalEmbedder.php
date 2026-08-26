<?php

namespace App\Modules\ImageTextSearch\Embeddings;

/**
 * Hand-rolled stand-in for a real CLIP-style cross-modal embedder — see the module
 * README for the full rationale. `embed()` is the only embedding function, shared by
 * both modalities: a seeded image's position comes from embedding a hand-authored
 * caption for it (see `SeededImageSeeder`), a live query is embedded the same way,
 * and both land in the same fixed set of themed keyword buckets, one per dimension —
 * the same trick `ConceptEmbedder` (semantic-search) uses for text alone.
 */
final class CrossModalEmbedder
{
    public const DIMENSIONS = 4;

    // Forest/green, water/sky/snow, golden hour, animals/rustic — see README.
    /** @var array<int, array<int, string>> */
    private const CLUSTERS = [
        ['forest', 'forests', 'green', 'greenery', 'tree', 'trees', 'woods', 'woodland', 'trail', 'stones', 'stone'],
        ['water', 'sea', 'ocean', 'wave', 'wake', 'river', 'blue', 'sky', 'cloudy', 'snow', 'peak', 'peaks', 'mountain', 'mountains', 'mist', 'misty', 'pine', 'pines', 'cold', 'cool', 'grey'],
        ['golden', 'gold', 'hour', 'sunset', 'sun', 'dusk', 'evening', 'warm', 'amber', 'glow', 'glowing', 'field', 'fields', 'hill', 'hills', 'hiker', 'hiking', 'orchard'],
        ['animal', 'animals', 'farm', 'rustic', 'brown', 'wooden', 'barn', 'shed', 'porch', 'hillside', 'dusty'],
    ];

    /** @return array<int, float> */
    public function embed(string $text): array
    {
        $vector = array_fill(0, self::DIMENSIONS, 0.0);

        foreach ($this->tokenize($text) as $word) {
            foreach (self::CLUSTERS as $index => $keywords) {
                if (in_array($word, $keywords, true)) {
                    $vector[$index]++;
                }
            }
        }

        return $this->normalize($vector);
    }

    /**
     * Cosine distance (1 - cosine similarity) between two unit vectors — 0 means identical.
     *
     * @param  array<int, float>  $a
     * @param  array<int, float>  $b
     */
    public function distance(array $a, array $b): float
    {
        $dot = 0.0;

        foreach ($a as $index => $value) {
            $dot += $value * $b[$index];
        }

        return 1 - $dot;
    }

    /** @return array<int, string> */
    private function tokenize(string $text): array
    {
        return preg_split('/[^a-z0-9]+/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param  array<int, float>  $vector
     * @return array<int, float>
     */
    private function normalize(array $vector): array
    {
        $magnitude = sqrt(array_sum(array_map(fn (float $v) => $v ** 2, $vector)));

        if ($magnitude === 0.0) {
            return $vector;
        }

        return array_map(fn (float $v) => $v / $magnitude, $vector);
    }
}
