<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_category_id')->constrained();
            $table->foreignId('destination_id')->constrained();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('badge_text')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('duration_value')->nullable();
            $table->string('duration_unit', 30)->nullable();
            $table->decimal('starting_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('cover_image')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->longText('important_information')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_popular')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('experience_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();
        });

        foreach (['experience_highlights', 'experience_inclusions', 'experience_exclusions'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
                $table->text('item');
                $table->unsignedInteger('display_order')->default(0)->index();
                $table->timestamps();
            });
        }

        Schema::create('experience_travel_style', function (Blueprint $table) {
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->foreignId('travel_style_id')->constrained()->cascadeOnDelete();
            $table->primary(['experience_id', 'travel_style_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experience_travel_style');
        Schema::dropIfExists('experience_exclusions');
        Schema::dropIfExists('experience_inclusions');
        Schema::dropIfExists('experience_highlights');
        Schema::dropIfExists('experience_images');
        Schema::dropIfExists('experiences');
    }
};
