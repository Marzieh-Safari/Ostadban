<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            
            $table->id();
            $table->string('full_name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'student', 'professor']);
            $table->boolean('is_approved')->default(false);
            $table->rememberToken();
            $table->timestamps();

            
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            
            $table->string('student_number')->nullable()->unique();
            $table->string('verification_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('major')->nullable();

            
            $table->string('faculty_number')->nullable()->unique();
            $table->string('department')->nullable();
            $table->float('average_rating')->default(0);
            $table->boolean('is_board_member')->default(false);
            $table->integer('teaching_experience')
                  ->unsigned()
                  ->default(0)
                  ->comment('تعداد سالهای تجربه تدریس استاد');
                  
            $table->integer('comments_count')
                  ->unsigned()
                  ->default(0)
                  ->comment('تعداد نظرات دریافتی برای استاد');
                  
            $table->string('avatar')
              ->nullable()
              ->comment('آدرس تصویر آواتار استاد');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};