<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employees Table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            
            // Link to NoahFace: This matches the 'User ID' or 'Badge Number' in NoahFace
            $table->string('noahface_id')->unique()->index(); 
            
            // Link to the Award System
            $table->foreignId('award_id')->nullable();
            
            // Critical: Matches the 'employment_type' string in your 'award_rates' table
            // e.g., 'Casual' or 'Full Time/Part Time'
            $table->string('employment_type'); 
            
            $table->timestamps();
        });

        // 2. Attendance Logs (NoahFace Events)
        // Schema::create('attendance_logs', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
        //     $table->dateTime('clock_time');
        //     $table->string('event_type'); // 'clock_in', 'clock_out', 'break_start', etc.
        //     $table->string('location')->nullable(); // e.g., 'Warehouse A'
            
        //     // Optional: Store the raw JSON from NoahFace webhook for debugging
        //     $table->json('raw_payload')->nullable(); 
            
        //     $table->timestamps();
        // });
    }

    public function down(): void
    {
       // Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('employees');
    }
};