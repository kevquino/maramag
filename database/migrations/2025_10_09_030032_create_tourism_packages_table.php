<?php
// database/migrations/2024_01_01_000000_create_tourism_packages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tourism_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location');
            $table->string('category'); // adventure, cultural, beach, mountain, etc.
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('duration_days')->default(1);
            $table->integer('duration_nights')->default(0);
            $table->text('inclusions')->nullable(); // JSON or text
            $table->text('exclusions')->nullable(); // JSON or text
            $table->text('itinerary')->nullable(); // JSON or text
            $table->string('difficulty_level')->default('easy'); // easy, moderate, difficult
            $table->integer('max_participants')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('featured_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tourism_packages');
    }
};