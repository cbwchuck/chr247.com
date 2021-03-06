<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medicals', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->integer('patient_id')->unsigned();
            $table->integer('created_by')->unsigned();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('medicals');
    }
}
