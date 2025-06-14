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
        Schema::create('security_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('device_type_id')->constrained('device_types')->onDelete('restrict');
            $table->string('name');
            $table->string('serial_number')->nullable()->unique();
            $table->string('location_description')->nullable();
            $table->string('qr_code_identifier')->nullable()->unique();
            $table->enum('status', ['operational', 'needs_maintenance', 'out_of_service', 'under_maintenance'])->default('operational');
            $table->timestamp('last_checked_at')->nullable();
            $table->foreignId('last_checked_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('next_maintenance_due')->nullable();
            $table->date('installation_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('security_devices');
    }
};
