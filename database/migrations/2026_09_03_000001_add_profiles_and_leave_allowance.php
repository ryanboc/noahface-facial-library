<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('employee')->index();
        });

        // Preserve access for accounts created before roles existed.
        DB::table('users')->update(['role' => 'executive']);

        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedSmallInteger('annual_leave_allowance')->default(20);
        });
    }

    public function down(): void
    {
        Schema::table('employees', fn (Blueprint $table) => $table->dropColumn('annual_leave_allowance'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
