<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // ایجاد کلید خارجی جدید
            $table->foreign('faculty_number')
                  ->references('faculty_number')
                  ->on('users') // ارجاع به جدول users
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['faculty_number']);
        });
    }
};