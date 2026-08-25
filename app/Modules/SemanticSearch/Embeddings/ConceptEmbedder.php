<?php

namespace App\Modules\SemanticSearch\Embeddings;

final class ConceptEmbedder
{
    public const DIMENSIONS = 8;

    /** @var array<int, array<int, string>> */
    private const CONCEPTS = [
        ['laptop', 'notebook', 'computer', 'pc', 'chromebook', 'ultrabook'],
        ['headphones', 'earbuds', 'earphones', 'headset', 'speaker', 'audio'],
        ['camera', 'dslr', 'mirrorless', 'lens', 'photography', 'photo'],
        ['treadmill', 'fitness', 'workout', 'exercise', 'gym', 'resistance'],
        ['blender', 'kitchen', 'cooking', 'mixer', 'cooker', 'culinary'],
        ['ebook', 'ereader', 'kindle', 'reading', 'book', 'pages'],
        ['console', 'gaming', 'controller', 'playstation', 'xbox', 'game'],
        ['smartwatch', 'watch', 'wearable', 'tracker', 'band', 'steps'],
    ];

    /** @return array<int, float> */
    public function embed(string $text): array
    {
        $vector = array_fill(0, count(self::CONCEPTS), 0.0);

        foreach ($this->tokenize($text) as $word) {
            foreach (self::CONCEPTS as $index => $synonyms) {
                if (in_array($word, $synonyms, true)) {
                    $vector[$index]++;
                }
            }
        }

        return $this->normalize($vector);
    }

    /** @param array<int, float> $a @param array<int, float> $b */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;

        foreach ($a as $index => $value) {
            $dot += $value * $b[$index];
        }

        return $dot;
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
