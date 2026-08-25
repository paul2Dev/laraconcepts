<?php

namespace App\Modules\SemanticSearch\Database\Seeders;

use App\Modules\SemanticSearch\Embeddings\ConceptEmbedder;
use App\Modules\SemanticSearch\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /** @var array<int, array{title: string, body: string}> */
    private const ARTICLES = [
        ['title' => 'UltraBook Pro 14', 'body' => 'A lightweight laptop built for developers on the move, 14-inch display, all-day battery.'],
        ['title' => 'ChromeBook Air', 'body' => 'An affordable computer for students, fast boot times and cloud-first storage.'],
        ['title' => 'SoundWave Buds', 'body' => 'True wireless earbuds with active noise cancellation for daily commutes.'],
        ['title' => 'BassPod Speaker', 'body' => 'A portable speaker with deep bass, perfect for outdoor gatherings.'],
        ['title' => 'PixelShot X200', 'body' => 'A mirrorless camera with a fast autofocus system for street photography.'],
        ['title' => 'ZoomLens 70-200mm', 'body' => 'A telephoto lens for capturing distant subjects with sharp detail.'],
        ['title' => 'RunTrack Treadmill', 'body' => 'A foldable treadmill with incline settings for home workouts.'],
        ['title' => 'FlexBand Resistance Set', 'body' => 'A set of resistance bands for a full-body workout routine.'],
        ['title' => 'ChefMix Blender', 'body' => 'A high-power blender for smoothies and soups, five speed settings.'],
        ['title' => 'SteamPot Cooker', 'body' => 'A multi-function cooker for steaming, boiling, and slow cooking.'],
        ['title' => 'PageTurn E-Reader', 'body' => 'A glare-free e-reader with weeks of battery life for book lovers.'],
        ['title' => 'StoryLight Reading Lamp', 'body' => 'A clip-on reading lamp with adjustable brightness for night reading.'],
        ['title' => 'PlayCore Console', 'body' => 'A next-gen gaming console with 4K output and fast load times.'],
        ['title' => 'GripPad Controller', 'body' => 'An ergonomic controller with programmable buttons for competitive gaming.'],
        ['title' => 'PulseFit Smartwatch', 'body' => 'A fitness tracker watch with heart-rate monitoring and sleep tracking.'],
        ['title' => 'TimeBand Classic', 'body' => 'A minimalist wearable band that tracks steps and notifications.'],
    ];

    public function run(): void
    {
        $embedder = new ConceptEmbedder;

        foreach (self::ARTICLES as $article) {
            Article::create([
                'title' => $article['title'],
                'body' => $article['body'],
                'embedding' => $embedder->embed($article['title'].' '.$article['body']),
            ]);
        }
    }
}
