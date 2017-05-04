<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteColumnUrlIsSend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('key', function (Blueprint $table) {
            $table->dropColumn('pk_is_send');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('key', function ($table) {
            $table->boolean('pk_is_send')->after('pk_is_used')->default(0)->comment = 'Key 是否发送';
        });
    }
}
