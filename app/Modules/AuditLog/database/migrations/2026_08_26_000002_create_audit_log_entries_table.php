<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log_entries', function (Blueprint $table) {
            $table->id();
            $table->string('actor');
            $table->string('action');
            $table->string('subject');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log_entries');
    }
};
