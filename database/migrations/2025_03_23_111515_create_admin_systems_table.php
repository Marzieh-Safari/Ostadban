<?php

// database/migrations/xxxx_xx_xx_create_admin_systems_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminSystemsTable extends Migration
{
    public function up()
    {
        Schema::create('admin_systems', function (Blueprint $table) {
            $table->id(); // شناسه اصلی
            $table->string('username')->unique(); // نام کاربری
            $table->string('email')->unique(); // ایمیل
            $table->string('password'); // رمز عبور
            $table->timestamps(); // زمان‌های ایجاد و به‌روزرسانی
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_systems');
    }
}