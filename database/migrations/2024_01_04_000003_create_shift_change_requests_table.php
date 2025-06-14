<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('cascade');
            $table->foreignId('requesting_user_id')->comment('The guard submitting the request')->constrained('users')->onDelete('cascade');
            
            $table->dateTime('requested_start_time')->nullable();
            $table->dateTime('requested_end_time')->nullable();
            $table->date('requested_day_off_date')->nullable(); 

            $table->enum('request_type', ['time_change', 'day_off_instead_of_shift', 'day_off_general'])->default('time_change');
            
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled_by_guard'])->default('pending');
            
            $table->foreignId('processed_by_user_id')->nullable()->comment('Supervisor/Admin who processed')->constrained('users')->onDelete('set null');
            $table->text('supervisor_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            
            $table->timestamps();
            $table->index(['requesting_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_change_requests');
    }
};
