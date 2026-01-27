<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            
            // Link to the Employee (who then links to the Award)
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // The core data
            $table->dateTime('clock_time');
            $table->string('event_type'); // 'clock_in', 'clock_out', 'break_start'
            $table->string('location')->nullable();
            
            // Store the full JSON from NoahFace for debugging/audit trails
            $table->json('raw_payload')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
