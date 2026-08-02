<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_category_id')->constrained();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('badge_text')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedSmallInteger('days');
            $table->unsignedSmallInteger('nights');
            $table->decimal('starting_price', 12, 2)->nullable();
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('price_note')->nullable();
            $table->unsignedSmallInteger('minimum_travelers')->default(1);
            $table->unsignedSmallInteger('maximum_travelers')->nullable();
            $table->string('tour_type')->nullable();
            $table->string('physical_level')->nullable();
            $table->text('perfect_for')->nullable();
            $table->text('accommodation_summary')->nullable();
            $table->text('transportation_summary')->nullable();
            $table->longText('cancellation_policy')->nullable();
            $table->text('support_text')->nullable();
            $table->longText('terms_and_conditions')->nullable();
            $table->string('itinerary_pdf')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_popular')->default(false)->index();
            $table->boolean('is_customizable')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('package_destination', function (Blueprint $table) {
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->primary(['package_id', 'destination_id']);
        });

        Schema::create('package_travel_style', function (Blueprint $table) {
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('travel_style_id')->constrained()->cascadeOnDelete();
            $table->primary(['package_id', 'travel_style_id']);
        });

        Schema::create('package_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('package_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('destination_name')->nullable();
            $table->string('accommodation_name')->nullable();
            $table->string('meals')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();
            $table->unique(['package_id', 'day_number']);
        });

        Schema::create('package_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('image_path')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();
        });

        foreach (['package_inclusions', 'package_exclusions'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->foreignId('package_id')->constrained()->cascadeOnDelete();
                $table->text('item');
                $table->unsignedInteger('display_order')->default(0)->index();
                $table->timestamps();
            });
        }

        Schema::create('package_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->longText('answer');
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('package_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('country')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('review');
            $table->string('customer_image')->nullable();
            $table->boolean('is_approved')->default(false)->index();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_reviews');
        Schema::dropIfExists('package_faqs');
        Schema::dropIfExists('package_exclusions');
        Schema::dropIfExists('package_inclusions');
        Schema::dropIfExists('package_highlights');
        Schema::dropIfExists('package_itineraries');
        Schema::dropIfExists('package_images');
        Schema::dropIfExists('package_travel_style');
        Schema::dropIfExists('package_destination');
        Schema::dropIfExists('packages');
    }
};
