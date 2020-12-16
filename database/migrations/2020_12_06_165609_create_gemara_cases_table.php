<?php

use App\Http\Controllers\GemaraCaseController;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\GemaraCase;

class CreateGemaraCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gemara_cases', function (Blueprint $table) {
            $table->id();

            $table->string('masechet', 100);
            $table->string('daf', 10);
            $table->string('gemara_text', 2048);
            $table->string('title', 1024)->nullable();
            $table->enum('din_type', GemaraCase::dinTypes);
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gemara_cases');
    }
}
