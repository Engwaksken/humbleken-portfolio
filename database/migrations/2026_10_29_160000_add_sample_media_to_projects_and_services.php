<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('sample_image')->nullable();
            $table->string('sample_video_url', 2048)->nullable();
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->string('sample_image')->nullable();
            $table->string('sample_video_url', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn(['sample_image', 'sample_video_url']);
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['sample_image', 'sample_video_url']);
        });
    }
};
