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
        // 1. Employee Shift Schedules (Roster)
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_off_day')->default(false);
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });

        // 2. Attendances (Presensi / Kehadiran)
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('date');
            
            // Check-in details
            $table->time('clock_in')->nullable();
            $table->decimal('in_latitude', 10, 8)->nullable();
            $table->decimal('in_longitude', 11, 8)->nullable();
            $table->string('in_photo')->nullable();
            $table->decimal('in_distance_meters', 8, 2)->nullable();
            
            // Check-out details
            $table->time('clock_out')->nullable();
            $table->decimal('out_latitude', 10, 8)->nullable();
            $table->decimal('out_longitude', 11, 8)->nullable();
            $table->string('out_photo')->nullable();
            $table->decimal('out_distance_meters', 8, 2)->nullable();

            // Status & Metrics
            $table->enum('status', ['present', 'late', 'early_leave', 'absent', 'leave', 'sick', 'permission'])->default('present');
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->integer('work_duration_minutes')->default(0);
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('schedules');
    }
};
