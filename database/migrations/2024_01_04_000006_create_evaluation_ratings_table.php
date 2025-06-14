<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluation_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_evaluation_id')->constrained('performance_evaluations')->onDelete('cascade');
            $table->foreignId('evaluation_criterion_id')->constrained('evaluation_criteria')->onDelete('cascade');
            $table->integer('score');
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->unique(['performance_evaluation_id', 'evaluation_criterion_id'], 'eval_perf_eval_criterion_unique');
        });
    }
    public function down()
    {
        Schema::dropIfExists('evaluation_ratings');
    }
};
