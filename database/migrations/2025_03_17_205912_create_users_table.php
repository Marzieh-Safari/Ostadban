<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // 🔹 فیلدهای اصلی مشترک بین همه کاربران
            $table->id();
            $table->string('full_name')->comment('نام کامل کاربر (برای همه انواع کاربران)');
            $table->string('username')->unique()->comment('شناسه کاربری منحصر به فرد');
            $table->string('email')->unique()->comment('آدرس ایمیل');
            $table->string('password')->comment('رمز عبور رمزنگاری شده');
            $table->enum('type', ['admin', 'student', 'professor'])->comment('نوع کاربر');
            $table->boolean('is_approved')->default(false)->comment('وضعیت تأیید (مخصوص اساتید و دانشجویان)');
            $table->rememberToken()->comment('توکن یادآوری برای احراز هویت');
            $table->timestamps();

            // 🔹 فیلدهای Audit (چه کسی رکورد را ایجاد/ویرایش کرده؟)
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('کاربر ایجادکننده');
            $table->foreignId('updated_by')->nullable()->constrained('users')->comment('کاربر به‌روزرساننده');

            // 🔹 فیلدهای مخصوص دانشجو
            $table->string('student_number')->nullable()->unique()->comment('شماره دانشجویی');
            $table->string('verification_token')->nullable()->comment('توکن تأیید ایمیل');
            $table->timestamp('token_expires_at')->nullable()->comment('تاریخ انقضای توکن');
            $table->string('phone')->nullable()->comment('شماره تماس');
            $table->string('major')->nullable()->comment('رشته تحصیلی');

            // 🔹 فیلدهای مخصوص استاد
            $table->string('faculty_number')->nullable()->unique()->comment('شماره پرسنلی استاد');
            $table->string('department')->nullable()->comment('دانشکده یا گروه آموزشی');
            $table->float('average_rating')->default(0)->comment('میانگین امتیاز دانشجویان به استاد');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};