<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('position')->nullable()->after('office');
            $table->string('avatar')->nullable()->after('position');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->integer('login_count')->default(0)->after('last_login_ip');
            $table->string('timezone')->default('UTC')->after('permissions');
            $table->string('locale')->default('en')->after('timezone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
