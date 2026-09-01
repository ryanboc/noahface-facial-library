<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('shift_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->string('role')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('published');
            $table->timestamps();
            $table->index(['employee_id', 'shift_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_shifts');
    }
};
