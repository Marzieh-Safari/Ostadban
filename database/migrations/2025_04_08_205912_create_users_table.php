<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // فیلدهای مشترک
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('type', ['admin', 'student', 'professor']);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            // فیلدهای مخصوص دانشجو
            $table->string('name')->nullable();
            $table->string('student_number')->nullable()->unique();
            $table->string('verification_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('major')->nullable();

            // فیلدهای مخصوص استاد
            $table->string('full_name')->nullable();
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