<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tractates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('english_name', 200)->unique();
            $table->integer('pages');
            $table->boolean('has_last_amud');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tractates');
    }
};
