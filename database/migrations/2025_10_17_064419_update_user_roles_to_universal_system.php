<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing roles to new system
        DB::table('users')->where('role', 'admin')->update(['role' => 'superadmin']);
        DB::table('users')->where('role', 'PIO Officer')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'PIO Staff')->update(['role' => 'staff']);
        DB::table('users')->where('role', 'user')->update(['role' => 'staff']);
    }

    public function down(): void
    {
        // Revert back to old roles if needed
        DB::table('users')->where('role', 'superadmin')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'admin')->update(['role' => 'PIO Officer']);
        DB::table('users')->where('role', 'staff')->update(['role' => 'PIO Staff']);
    }
};