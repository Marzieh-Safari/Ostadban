<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();

            //ارتباط با کورس
            $table->foreignId('course_id');
            
            // ارتباط با دانشجو (اصلاح‌شده)
            $table->foreignId('student_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // ارتباط با استاد (اصلاح‌شده)
            $table->foreignId('professor_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->integer('rating')->between(1, 5);
            $table->text('comment')->nullable();
            $table->timestamps();

            // ایندکس ترکیبی (اصلاح‌شده)
            $table->index(['student_id', 'professor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
};