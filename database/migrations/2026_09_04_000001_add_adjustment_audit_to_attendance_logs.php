<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->boolean('is_manual')->default(false)->after('raw_payload');
            $table->text('adjustment_reason')->nullable()->after('is_manual');
            $table->foreignId('adjusted_by')->nullable()->after('adjustment_reason')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adjusted_by');
            $table->dropColumn(['is_manual', 'adjustment_reason']);
        });
    }
};
