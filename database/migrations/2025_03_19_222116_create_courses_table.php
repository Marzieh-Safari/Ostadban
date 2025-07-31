<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table ->string('avatar');
            $table ->string('slug') ->nullable();
            $table->text('description')->nullable();
            
            // ارتباط با استاد (اصلاح‌شده)
            $table->foreignId('professor_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->string('course_code')->unique();
            $table->integer('credits')->default(3);
            $table->timestamps();

            // ایندکس‌ها
            $table->index('professor_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};