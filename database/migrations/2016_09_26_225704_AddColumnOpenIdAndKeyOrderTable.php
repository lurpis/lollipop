<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOpenIdAndKeyOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order', function (Blueprint $table) {
            $table->char('po_open_id', 32)->after('po_charge_id')->nullable()->comment = 'Open ID';
            $table->char('po_key', 16)->after('po_open_id')->nullable()->comment = '订单关联 Key';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order', function (Blueprint $table) {
            $table->dropColumn('po_open_id');
            $table->dropColumn('po_key');
        });
    }
}
