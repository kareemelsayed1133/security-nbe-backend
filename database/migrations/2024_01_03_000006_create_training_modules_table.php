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
        Schema::create('training_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_category_id')->constrained('training_categories')->onDelete('cascade');
            $table->string('title_ar');
            $table->text('description_ar')->nullable();
            $table->enum('content_type', ['video_url', 'pdf_url', 'text_content', 'interactive_simulation_url'])->default('text_content');
            $table->string('content_url', 1000)->nullable();
            $table->longText('text_content_ar')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('difficulty_level')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->integer('order_in_category')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_modules');
    }
};
