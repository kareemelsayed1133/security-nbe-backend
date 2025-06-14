<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('security_device_id')->constrained('security_devices')->onDelete('cascade');
            $table->foreignId('checked_by_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('check_time')->useCurrent();
            $table->enum('status_reported', ['operational', 'needs_maintenance', 'out_of_service']);
            $table->text('notes')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('device_checks');
    }
};
