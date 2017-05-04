<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function(Blueprint $table) {
            $table->char('pu_id', 16)->comment = 'User ID';
            $table->string('pu_username', 64)->comment = 'Username';
            $table->string('pu_url', 200)->comment = '短链接';
            $table->string('pu_full_url', 500)->comment = '长链接';
            $table->string('pu_today', 3)->default(0)->comment = '本日有效次数';
            $table->string('pu_tswk', 3)->default(0)->comment = '本周有效次数';
            $table->string('pu_touch_total', 11)->default(0)->comment = '总共触发次数';
            $table->string('pu_pop_total', 11)->default(0)->comment = '总共获得棒棒糖总数';
            $table->boolean('pu_is_vip')->default(0)->comment = '是否是 VIP';
            $table->timestamp('pu_vip_expire_at')->nullable()->comment = 'VIP 到期时间';
            $table->timestamps();
            $table->primary('pu_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user');
    }
}
