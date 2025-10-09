<?php
// database/migrations/2024_01_01_create_full_disclosures_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('full_disclosures', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'is_published']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('full_disclosures');
    }
};