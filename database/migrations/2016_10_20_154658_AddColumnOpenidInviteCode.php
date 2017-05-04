<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOpenidInviteCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('open_id', function (Blueprint $table) {
            $table->char('poi_invite_code', 8)->after('poi_is_subscribe')->nullable()->comment = '邀请码';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('open_id', function (Blueprint $table) {
            $table->dropColumn('poi_invite_code');
        });
    }
}
