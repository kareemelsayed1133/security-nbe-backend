<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->unique();
            $table->text('description_ar')->nullable();
            $table->string('icon_class')->nullable();
            $table->integer('check_frequency_days')->nullable()->comment('Recommended check frequency in days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('device_types'); }
};
