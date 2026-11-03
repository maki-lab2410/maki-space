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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('room');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            
            $table->enum('borrower_category', ['Lecturer', 'Public']);
            $table->enum('schedule_type', ['Regular', 'Incidental'])->default('Regular');
            
            // If Category = Lecturer
            $table->foreignId('lecturer_id')->nullable()->constrained('lecturers')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('course_class_id')->nullable()->constrained('course_classes')->cascadeOnDelete();
            
            // If Category = Public
            $table->string('general_borrower_name')->nullable();
            $table->string('general_purpose')->nullable();
            
            $table->enum('status', ['Scheduled', 'Completed', 'Cancelled'])->default('Scheduled');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
