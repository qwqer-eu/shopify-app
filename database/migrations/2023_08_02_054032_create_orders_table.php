<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string("shop_id");
            $table->string("shop_order_id");
            $table->string("order_id");
            $table->string("delivery_area_id");
            $table->string("client_id");
            $table->string("courier_id")->nullable();
            $table->string("trading_point_id");
            $table->string("is_parent");
            $table->string("parent_id")->nullable();
            $table->string("status");
            $table->string("type");
            $table->string("real_type");
            $table->string("category");
            $table->string("pickup_datetime");
            $table->string("is_round_trip");
            $table->string("courier_vehicle_id")->nullable();
            $table->string("courier_transport_mode")->nullable();
            $table->string("dropdowns");
            $table->string("client_price");
            $table->string("client_distance");
            $table->string("client_distance_price");
            $table->string("client_adjustments_price");
            $table->string("client_dropdowns_price");
            $table->string("client_pickup_price");
            $table->string("direct_distance");
            $table->string("distance");
            $table->string("accepted_at")->nullable();
            $table->string("picked_up_at")->nullable();
            $table->string("finished_at")->nullable();
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
        Schema::dropIfExists('orders');
    }
}
