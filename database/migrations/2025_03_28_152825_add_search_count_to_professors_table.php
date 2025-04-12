<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('professors', function (Blueprint $table) {
            $table->unsignedInteger('search_count')->default(0)->after('average_rating');
        });
    }

    public function down()
    {
        Schema::table('professors', function (Blueprint $table) {
            $table->dropColumn('search_count');
        });
    }
};