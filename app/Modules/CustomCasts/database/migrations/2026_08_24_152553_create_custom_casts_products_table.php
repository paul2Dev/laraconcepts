<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_casts_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price_amount');
            $table->char('price_currency', 3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_casts_products');
    }
};
