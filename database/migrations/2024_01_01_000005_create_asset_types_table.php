<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->unique();
            $table->string('icon_class')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('asset_types'); }
};
