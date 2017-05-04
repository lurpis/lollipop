<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key', function(Blueprint $table) {
            $table->char('pk_key', 16)->comment = 'Key';
            $table->string('pk_day', 11)->comment = 'Key 的增量天数';
            $table->boolean('pk_is_used')->default(0)->comment = 'Key 是否使用';
            $table->timestamp('pk_used_at')->nullable()->comment = 'Key 使用的时间';
            $table->timestamps();
            $table->primary('pk_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('key');
    }
}
