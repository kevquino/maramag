<?php
// database/migrations/2024_01_01_000010_create_sangguniang_bayan_members_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sangguniang_bayan_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->string('position_type'); // regular, sk_president, liga_president, ip_representative
            $table->text('bio')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('committees')->nullable(); // JSON array of committees
            $table->string('district')->nullable();
            $table->string('term_start')->nullable();
            $table->string('term_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sangguniang_bayan_members');
    }
};