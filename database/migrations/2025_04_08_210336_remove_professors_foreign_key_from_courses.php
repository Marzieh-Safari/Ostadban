<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // بررسی وجود کلید خارجی قبل از حذف
            if (Schema::hasColumn('courses', 'faculty_number')) {
                $table->dropForeign(['faculty_number']);
            }
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            // در صورت نیاز به بازگشت (Rollback)
            $table->foreign('faculty_number')
                  ->references('faculty_number')
                  ->on('professors') // اگر جدول professors هنوز وجود دارد
                  ->onDelete('cascade');
        });
    }
};