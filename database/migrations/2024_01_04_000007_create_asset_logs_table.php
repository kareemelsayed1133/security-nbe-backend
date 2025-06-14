<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('user_id')->comment('The guard')->constrained('users')->onDelete('cascade');
            $table->foreignId('processed_by_user_id')->comment('The supervisor/admin')->constrained('users');
            $table->enum('action', ['assigned', 'returned', 'reported_lost', 'reported_damaged']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('asset_logs');
    }
};
