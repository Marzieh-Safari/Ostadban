<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();

            
            $table->foreignId('course_id');
            
            
            $table->foreignId('student_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            
            $table->foreignId('professor_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->integer('rating')->between(1, 5);
            $table->text('comment')->nullable();
            $table->timestamps();

            
            $table->index(['student_id', 'professor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
};