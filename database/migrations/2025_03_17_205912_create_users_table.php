<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // فیلدهای اصلی
            $table->id();
            $table->string('full_name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('type', ['admin', 'student', 'professor']);
            $table->boolean('is_approved')->default(false);
            $table->rememberToken();
            $table->timestamps();

            // فیلدهای Audit (اصلاح‌شده)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // فیلدهای دانشجو
            $table->string('student_number')->nullable()->unique();
            $table->string('verification_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('major')->nullable();

            // فیلدهای استاد
            $table->string('faculty_number')->nullable()->unique();
            $table->string('department')->nullable();
            $table->float('average_rating')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};