<?php

namespace App\Modules\ImageSimilarity\Embeddings;

use GdImage;
use InvalidArgumentException;

final class ImageEmbedder
{
    private const GRID = 4;

    public const DIMENSIONS = self::GRID * self::GRID * 3;

    /** @return array<int, float> */
    public function embed(string $binary): array
    {
        $source = @imagecreatefromstring($binary);

        if (! $source instanceof GdImage) {
            throw new InvalidArgumentException('Unable to decode image data.');
        }

        $resized = imagecreatetruecolor(self::GRID, self::GRID);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, self::GRID, self::GRID, imagesx($source), imagesy($source));

        $vector = [];
        for ($y = 0; $y < self::GRID; $y++) {
            for ($x = 0; $x < self::GRID; $x++) {
                $rgb = imagecolorsforindex($resized, imagecolorat($resized, $x, $y));
                $vector[] = $rgb['red'] / 255;
                $vector[] = $rgb['green'] / 255;
                $vector[] = $rgb['blue'] / 255;
            }
        }

        imagedestroy($source);
        imagedestroy($resized);

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
