<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_activities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->string('type'); // news, bid, disclosure, etc.
            $table->json('metadata')->nullable(); // Additional data
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activities');
    }
};