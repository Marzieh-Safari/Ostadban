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
    Schema::create('feedbacks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('student_id')->constrained()->onDelete('cascade');
        $table->string('faculty_number'); // تعریف ستون faculty_number
        $table->foreign('faculty_number')->references('faculty_number')->on('professors')->onDelete('cascade'); // اتصال به جدول professors
        $table->integer('rating')->between(1, 5);
        $table->text('comment')->nullable();
        $table->timestamps();
    });
}
    public function down()
{
    Schema::dropIfExists('professors');
}
};
