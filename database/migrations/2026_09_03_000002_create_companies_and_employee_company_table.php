<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('company_employee', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->primary(['company_id', 'employee_id']);
        });

        DB::table('companies')->insert(collect([
            'Inglewood Farms',
            'Country Synergy',
            'Country Heritage Feeds',
            'Next Office',
            'Eden Farms',
        ])->map(fn (string $name) => ['name' => $name, 'created_at' => now(), 'updated_at' => now()])->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('company_employee');
        Schema::dropIfExists('companies');
    }
};
