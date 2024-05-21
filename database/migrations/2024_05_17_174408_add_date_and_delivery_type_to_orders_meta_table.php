<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateAndDeliveryTypeToOrdersMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_meta', function (Blueprint $table) {
            $table->timestamp('date')
                ->nullable()
                ->after('status');
            $table->string('delivery_type', 20)
                ->default('QWQER_EVENING')
                ->after('customer_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_meta', function (Blueprint $table) {
            $table->dropColumn(['date', 'delivery_type']);
        });
    }
}
