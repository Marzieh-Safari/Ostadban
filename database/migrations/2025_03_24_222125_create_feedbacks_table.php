<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            
            // ارتباط با دانشجو (از جدول users)
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
                  ->references('id')
                  ->on('users')
                  ->where('type', 'student') // فقط کاربران دانشجو
                  ->onDelete('cascade');
            
            // ارتباط با استاد (از جدول users)
            $table->string('professor_faculty_number');
            $table->foreign('professor_faculty_number')
                  ->references('faculty_number')
                  ->on('users')
                  ->where('type', 'professor') // فقط کاربران استاد
                  ->onDelete('cascade');
            
            $table->integer('rating')->between(1, 5);
            $table->text('comment')->nullable();
            $table->timestamps();
            
            // ایندکس‌ها برای بهبود کارایی
            $table->index('student_id');
            $table->index('professor_faculty_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
};