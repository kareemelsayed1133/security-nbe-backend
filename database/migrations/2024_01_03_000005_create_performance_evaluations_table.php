<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guard_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('supervisor_user_id')->constrained('users')->onDelete('cascade');
            $table->date('evaluation_date');
            $table->string('evaluation_period')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('supervisor_comments')->nullable();
            $table->text('guard_feedback')->nullable();
            $table->enum('status', ['draft', 'finalized', 'acknowledged_by_guard'])->default('draft');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('performance_evaluations');
    }
};
