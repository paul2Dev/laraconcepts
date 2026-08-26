<?php

use App\Modules\ImageTextSearch\Embeddings\CrossModalEmbedder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_text_search_images', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->longText('image');
            $table->vector('embedding', CrossModalEmbedder::DIMENSIONS);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_text_search_images');
    }
};
