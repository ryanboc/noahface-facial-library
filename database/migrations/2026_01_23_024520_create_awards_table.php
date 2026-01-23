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
        
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pay_guide_link')->nullable(); // Links from 
            $table->timestamps();
        });

        
        Schema::create('award_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('award_id')->constrained()->onDelete('cascade');
            $table->string('employment_type'); // 'Casual', 'Full Time/Part Time'
            $table->string('category');        // 'Overtime', 'Public Holiday', 'Night', etc.
            
           
            $table->string('rate_value'); 
            $table->timestamps();
        });

       
        Schema::create('award_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('award_id')->constrained()->onDelete('cascade');
            
            // Text fields to accommodate verbose rules like "10 hours-Can be extended..." 
            $table->text('hours_per_day_rule')->nullable();
            $table->text('leading_hand_allowance')->nullable();
            $table->text('meal_allowance')->nullable();
            $table->text('paid_break_rule')->nullable();
            $table->text('unpaid_break_rule')->nullable();
            $table->text('remarks')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('award_conditions');
        Schema::dropIfExists('award_rates');
        Schema::dropIfExists('awards');
    }
};
