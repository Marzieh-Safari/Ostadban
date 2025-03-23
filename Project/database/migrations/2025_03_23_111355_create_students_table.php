<?php

// database/migrations/xxxx_xx_xx_create_students_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id(); // شناسه اصلی
            $table->string('name'); // نام دانشجو
            $table->string('email')->unique(); // ایمیل
            $table->string('phone')->nullable(); // شماره تلفن
            $table->string('major'); // رشته تحصیلی
            $table->timestamps(); // زمان‌های ایجاد و به‌روزرسانی
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
}