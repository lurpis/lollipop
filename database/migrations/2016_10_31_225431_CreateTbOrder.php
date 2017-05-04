<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taobao', function(Blueprint $table) {
            $table->char('ptb_tid', 22)->comment = 'Taobao Tid';
            $table->string('ptb_seller_nick', 100)->comment = '出售者';
            $table->string('ptb_buyer_nick', 100)->comment = '购买者';
            $table->string('ptb_buyer_message', 500)->nullable()->comment = '购买者留言';
            $table->char('ptb_key', 16)->nullable()->comment = '购买的 Key';
            $table->decimal('ptb_price', 10, 2)->default(0)->comment = '价格';
            $table->char('ptb_num', 3)->default(0)->comment = '购买数量';
            $table->decimal('ptb_total_fee', 10, 2)->default(0)->comment = '总价';
            $table->decimal('ptb_payment', 10, 2)->default(0)->comment = '付款金额';
            $table->string('ptb_orders', 500)->nullable()->comment = '关联订单';
            $table->string('ptb_url', 200)->nullable()->comment = '短链接';
            $table->boolean('ptb_is_deliver')->default(0)->comment = '订单是否发货';
            $table->boolean('ptb_is_wrong')->default(0)->comment = '订单是否为错误 url';
            $table->timestamp('ptb_pay_time')->nullable()->comment = '订单购买时间';
            $table->timestamp('ptb_created_time')->nullable()->comment = '订单创建时间';
            $table->timestamps();
            $table->primary('ptb_tid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('taobao');
    }
}
