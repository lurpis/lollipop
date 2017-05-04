<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnKeyExtend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('key', function (Blueprint $table) {
            $table->tinyInteger('pk_type')->after('pk_send_open_id')->default(0)->comment = '0 默认 1 关注 2 推荐';
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
            $table->dropColumn('pk_type');
        });
    }
}
