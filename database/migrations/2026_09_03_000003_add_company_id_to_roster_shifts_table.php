<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roster_shifts', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('employee_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'shift_date']);
        });

        DB::table('roster_shifts')->orderBy('id')->each(function ($shift) {
            $companyId = DB::table('company_employee')->where('employee_id', $shift->employee_id)->orderBy('company_id')->value('company_id');
            if ($companyId) {
                DB::table('roster_shifts')->where('id', $shift->id)->update(['company_id' => $companyId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('roster_shifts', fn (Blueprint $table) => $table->dropConstrainedForeignId('company_id'));
    }
};
