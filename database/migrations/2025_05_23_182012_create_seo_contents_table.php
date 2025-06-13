<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_contents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->text('seo_description')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_meta_description')->nullable();
            $table->string('check_result')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_contents');
    }
};
