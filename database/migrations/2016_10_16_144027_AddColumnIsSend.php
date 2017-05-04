<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsSend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('key', function (Blueprint $table) {
            $table->char('pk_send_open_id', 32)->after('pk_is_used')->nullable()->comment = 'Key 发送的 OpenID';
            $table->boolean('pk_is_send')->after('pk_is_used')->default(0)->comment = 'Key 是否发送';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('key', function (Blueprint $table) {
            $table->dropColumn('pk_is_send');
            $table->dropColumn('pk_send_open_id');
        });
    }
}
