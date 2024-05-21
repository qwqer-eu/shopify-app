<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApiAndShippingMethodsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('api', 20)
                ->after('api_key')
                ->default('Live');
            $table->boolean('is_carrier_service_available')
                ->default(false)
                ->after('order_category');
            $table->json('carrier_service_shipping_rates')
                ->nullable()
                ->after('is_carrier_service_available');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'api',
                'is_carrier_service_available',
                'carrier_service_shipping_rates',
            ]);
        });
    }
}
