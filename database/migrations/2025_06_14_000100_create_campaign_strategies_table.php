<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_strategies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('google_campaign_id')->index();
            $table->string('campaign_name');
            $table->text('strategy')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'google_campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_strategies');
    }
};
