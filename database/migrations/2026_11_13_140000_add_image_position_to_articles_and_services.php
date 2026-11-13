<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('image_position', 20)->nullable()->after('featured_image_alt');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->string('image_position', 20)->nullable()->after('sample_image');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('image_position');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('image_position');
        });
    }
};
