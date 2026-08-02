<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained();
            $table->string('customer_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('country')->nullable();
            $table->date('preferred_start_date')->nullable();
            $table->date('preferred_end_date')->nullable();
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->text('message')->nullable();
            $table->string('status')->default('new')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_tour_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('country')->nullable();
            $table->date('arrival_date')->nullable();
            $table->date('departure_date')->nullable();
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('accommodation_preference')->nullable();
            $table->string('transport_preference')->nullable();
            $table->text('special_requirements')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('quotation_sent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_tour_request_destination', function (Blueprint $table) {
            $table->foreignId('custom_tour_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->primary(['custom_tour_request_id', 'destination_id']);
        });

        Schema::create('custom_tour_request_travel_style', function (Blueprint $table) {
            $table->foreignId('custom_tour_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('travel_style_id')->constrained()->cascadeOnDelete();
            $table->primary(['custom_tour_request_id', 'travel_style_id']);
        });

        Schema::create('contact_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('new')->index();
            $table->boolean('is_read')->default(false)->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_enquiries');
        Schema::dropIfExists('custom_tour_request_travel_style');
        Schema::dropIfExists('custom_tour_request_destination');
        Schema::dropIfExists('custom_tour_requests');
        Schema::dropIfExists('package_enquiries');
    }
};
