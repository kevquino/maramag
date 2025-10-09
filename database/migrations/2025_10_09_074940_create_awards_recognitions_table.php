<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('awards_recognitions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('awarding_body');
            $table->string('category');
            $table->date('award_date');
            $table->date('received_date')->nullable();
            $table->string('location')->nullable();
            $table->string('award_type'); // national, regional, local, international
            $table->string('scope'); // municipal, provincial, regional, national, international
            $table->text('significance')->nullable();
            $table->text('criteria')->nullable();
            $table->string('recipient_type'); // individual, team, department, organization
            $table->string('recipient_name');
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->json('supporting_documents')->nullable(); // JSON array of file paths
            $table->string('featured_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['category', 'is_active']);
            $table->index(['award_type', 'is_active']);
            $table->index(['scope', 'is_active']);
            $table->index(['award_date', 'is_active']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('awards_recognitions');
    }
};