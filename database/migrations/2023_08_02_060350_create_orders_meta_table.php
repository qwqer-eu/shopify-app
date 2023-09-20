<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_meta', function (Blueprint $table) {
            $table->id();
            $table->string('shop_id');
            $table->string('order_id');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('shipping_address');
            $table->string('billing_address');
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
        Schema::dropIfExists('orders_meta');
    }
}
