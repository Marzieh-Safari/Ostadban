<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            
            // ارتباط با استاد از طریق faculty_number (به جای professor_id)
            $table->string('professor_faculty_number')->comment('شماره پرسنلی استاد');
            $table->foreign('professor_faculty_number')
                  ->references('faculty_number')
                  ->on('users')
                  ->where('type', 'professor') // تضمین می‌کند فقط اساتید انتخاب شوند
                  ->onDelete('cascade');
            
            // فیلدهای دیگر
            $table->string('course_code')->unique();
            $table->integer('credits')->default(3);
            $table->timestamps();
            
            // ایندکس برای بهبود کارایی
            $table->index('professor_faculty_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};