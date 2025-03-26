<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('professors', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('password');
        $table->string('faculty_number');
        $table->boolean('is_approved')->default(false);
        $table->string('department');
        $table->float('average_rating')->default(0);
        $table->timestamps();
    });
}
};
