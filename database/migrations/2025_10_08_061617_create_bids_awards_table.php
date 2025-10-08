<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_bids_awards_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bids_awards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('reference_number')->unique();
            $table->string('bid_type'); // open_tender, closed_tender, quotation, etc.
            $table->decimal('estimated_budget', 15, 2)->nullable();
            $table->date('bid_opening_date')->nullable();
            $table->date('bid_closing_date');
            $table->date('award_date')->nullable();
            $table->string('status'); // draft, published, opened, evaluated, awarded, cancelled
            $table->boolean('is_featured')->default(false);
            $table->string('awarded_to')->nullable();
            $table->decimal('awarded_amount', 15, 2)->nullable();
            $table->text('award_remarks')->nullable();
            $table->json('documents')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bids_awards');
    }
};