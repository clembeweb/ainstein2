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
        Schema::create('ai_article_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_article_job_id')->constrained()->cascadeOnDelete();
            $table->json('step1')->nullable();
            $table->json('step2')->nullable();
            $table->json('step3')->nullable();
            $table->json('step4')->nullable();
            $table->json('step5')->nullable();
            $table->json('step6')->nullable();
            $table->json('step7')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_article_steps');
    }
};
