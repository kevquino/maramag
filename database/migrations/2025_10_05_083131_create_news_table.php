<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->enum('category', [
                'Business', 
                'Finance', 
                'Events', 
                'Partnerships', 
                'Sustainability', 
                'Company News',
                'Announcement',
                'Update',
                'Event',
                'Maintenance'
            ])->default('Company News');
            $table->enum('status', ['published', 'draft', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index('status');
            $table->index('category');
            $table->index('is_featured');
            $table->index('published_at');
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};