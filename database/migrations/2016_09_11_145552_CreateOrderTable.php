<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function(Blueprint $table) {
            $table->char('po_order_no', 18)->comment = '订单号';
            $table->char('po_charge_id', 27)->comment = 'Charge ID';
            $table->boolean('po_paid')->default(0)->comment = '订单是否支付';
            $table->boolean('po_consume')->default(0)->comment = '订单是否消费';
            $table->decimal('po_amount', 10, 2)->default(0)->comment = '订单金额';
            $table->string('po_channel', 32)->comment = '支付渠道';
            $table->timestamp('po_expired_time')->nullable()->comment = '订单超市时间';
            $table->timestamp('po_paid_time')->nullable()->comment = '订单支付时间';
            $table->string('po_subject', 32)->comment = '订单名称';
            $table->string('po_body', 128)->comment = '订单内容';
            $table->string('po_client_ip', 30)->comment = '订单客户端IP';
            $table->string('po_metadata', 128)->comment = '订单详情';
            $table->string('po_description', 255)->nullable()->comment = '订单描述';
            $table->timestamps();
            $table->primary('po_order_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('order');
    }
}
