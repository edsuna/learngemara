<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\GemaraCase;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gemara_cases', function (Blueprint $table) {
            $table->id();
            $table->string('masechet', 100);
            $table->string('daf', 10);
            $table->string('gemara_text', 2048);
            $table->string('title', 1024)->nullable();
            $table->string('din_type', 20);
            $table->string('act', 1024);
            $table->boolean('public');

            foreach (GemaraCase::inputConditions as $inputCondition) {
                $table->string($inputCondition, 1024)->nullable();
                $table->boolean($inputCondition . '_nr');
            }

            $table->foreignId('user_id');
            $table->timestamps();
            $table->foreign('masechet')->references('english_name')->on('tractates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gemara_cases');
    }
};
