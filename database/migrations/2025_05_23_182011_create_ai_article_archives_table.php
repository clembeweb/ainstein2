<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_article_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_article_job_id')->constrained()->cascadeOnDelete();
            $table->string('keyword');
            $table->mediumText('html')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_article_archives');
    }
};
