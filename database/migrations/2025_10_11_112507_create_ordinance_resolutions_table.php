<?php
// database/migrations/2024_01_01_000000_create_ordinance_resolutions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordinance_resolutions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('number')->unique();
            $table->enum('type', ['ordinance', 'resolution']);
            $table->text('description')->nullable();
            $table->date('date_approved');
            $table->date('date_effectivity')->nullable();
            $table->string('sponsor')->nullable();
            $table->text('co_sponsors')->nullable();
            $table->enum('status', ['active', 'amended', 'repealed', 'pending']);
            $table->text('amendatory_to')->nullable();
            $table->text('repealed_by')->nullable();
            $table->json('categories')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['type', 'status']);
            $table->index(['date_approved']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordinance_resolutions');
    }
};