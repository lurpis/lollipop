<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('open_id', function(Blueprint $table) {
            $table->increments('poi_id')->comment = 'User ID';
            $table->char('poi_open_id', 32)->comment = 'Open ID';
            $table->char('poi_rec_open_id', 32)->nullable()->comment = '推荐人 Open ID';
            $table->string('poi_code_url', 200)->nullable()->comment = '推广二维码 Url';
            $table->string('poi_code_ticket', 500)->nullable()->comment = '推广二维码 Ticket';
            $table->string('poi_media_id', 200)->nullable()->comment = '临时素材 ID';
            $table->string('poi_media_expire_at', 32)->nullable()->comment = '临时素材过期时间';
            $table->boolean('poi_is_subscribe')->comment = '是否关注公众号';
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('open_id');
    }
}
