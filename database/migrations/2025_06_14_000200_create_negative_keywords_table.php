<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negative_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_strategy_id')
                  ->constrained('campaign_strategies')
                  ->cascadeOnDelete();
            $table->string('adgroup_name')->nullable();
            $table->string('keyword');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negative_keywords');
    }
};
