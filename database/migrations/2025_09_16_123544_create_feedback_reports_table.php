<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackReportsTable extends Migration
{
    public function up()
    {
        Schema::create('feedback_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable(); 
            $table->unsignedBigInteger('user_id'); 
            $table->enum('reason', ['abuse', 'irrelevant', 'spam', 'misleading', 'other']);
            $table->string('details', 500)->nullable();
            $table->timestamps();

            // روابط
            $table->foreign('feedback_id')->references('id')->on('feedback')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback_reports');
    }
}