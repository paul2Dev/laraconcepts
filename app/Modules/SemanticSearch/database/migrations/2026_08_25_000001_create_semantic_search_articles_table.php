<?php

use App\Modules\SemanticSearch\Embeddings\ConceptEmbedder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semantic_search_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->vector('embedding', ConceptEmbedder::DIMENSIONS);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semantic_search_articles');
    }
};
