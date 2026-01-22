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
        Schema::create('noahface_events', function (Blueprint $table) {
            $table->id();
        $table->bigInteger('eventid')->nullable();
        $table->timestamp('utc')->nullable();
        $table->timestamp('time')->nullable();
        $table->string('org')->nullable();
        $table->string('site')->nullable();
        $table->string('device')->nullable();
        $table->string('devid')->nullable();
        $table->string('type')->nullable();
        $table->string('detail')->nullable();
        $table->string('method')->nullable();
        $table->bigInteger('userid')->nullable();
        $table->string('number')->nullable();
        $table->string('firstname')->nullable();
        $table->string('lastname')->nullable();
        $table->string('cardnum')->nullable();
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->decimal('altitude', 10, 7)->nullable();
        $table->decimal('accuracy', 10, 7)->nullable();
        $table->decimal('temperature', 5, 2)->nullable();
        $table->boolean('elevated')->nullable();
        $table->string('timing')->nullable();
        $table->string('sentiment')->nullable();
        $table->string('usertype')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noahface_events');
    }
};
