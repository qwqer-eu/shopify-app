<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Osiset\BasicShopifyAPI\ResponseAccess;

class ViewController extends Controller
{
    public function welcome(): View
    {
        $shop = auth()->user();
        $is_carrier_service_available = false;

        if (!$shop instanceof User) {
            return view('welcome')
                ->with([
                    'is_carrier_service_available' => $is_carrier_service_available,
                    'selectable_shipping_rates' => [],
                ]);
        }
        $shop_id = $shop->id;

        $selectable_shipping_rates = [];
        $shopify_shipping_zones = $shop
            ->api()
            ->rest('GET', '/admin/api/2024-04/shipping_zones.json');
        $available_shipping_zones = [];

        if (isset($shopify_shipping_zones['body']['shipping_zones'])) {
            if ($shopify_shipping_zones['body']['shipping_zones'] instanceof ResponseAccess) {
                $available_shipping_zones = $shopify_shipping_zones['body']['shipping_zones']->toArray();
            } elseif (is_array($shopify_shipping_zones['body']['shipping_zones'])) {
                $available_shipping_zones = $shopify_shipping_zones['body']['shipping_zones'];
            }
        }

        foreach ($available_shipping_zones as $shipping_zone) {
            if (isset($shipping_zone['name']) && !isset($selectable_shipping_rates[$shipping_zone['name']])) {
                $shipping_zone_rates = array_column(array_merge(
                    $shipping_zone['weight_based_shipping_rates'], $shipping_zone['price_based_shipping_rates']
                ), 'name');

                if (!empty($shipping_zone_rates)) {
                    $selectable_shipping_rates[$shipping_zone['name']] = $shipping_zone_rates;
                }
            }
        }

        // delete previous carriers
//        $carrier_services = $shop->api()->rest('GET', '/admin/api/2024-04/carrier_services.json');
//        if (isset($carrier_services['body']->container['carrier_services'])) {
//            foreach ($carrier_services['body']->container['carrier_services'] as $carrier_service) {
//                if (
//                    isset($carrier_service['id'])
//                    && isset($carrier_service['name'])
//                    && str_contains($carrier_service['name'], 'QWQER')
//                ) {
//                    $shop->api()->rest('DELETE', "/admin/api/2024-04/carrier_services/{$carrier_service['id']}.json");
//                }
//            }
//        }

        $carrier_services_response = $shop
            ->api()
            ->rest(
                'POST',
                '/admin/api/2024-04/carrier_services.json',
                [
                    'carrier_service' => [
                        'name' => 'QWQER Express',
                        'callback_url' => url('add-rates'),
                        'service_discovery' => true,
                    ],
                ],
            );

        if (
            isset($carrier_services_response['exception'])
            && is_array($carrier_services_response['body']['base'])
            && in_array('QWQER Express is already configured', $carrier_services_response['body']['base'])
            || !isset($carrier_services_response['exception'])
        ) {
            $is_carrier_service_available = true;
        }

        $this->setIsCarrierServiceAvailable($shop_id, $is_carrier_service_available);

        return view('welcome')
            ->with([
                'is_carrier_service_available' => $is_carrier_service_available,
                'selectable_shipping_rates' => $selectable_shipping_rates ?? [],
            ]);
    }

    private function setIsCarrierServiceAvailable(int $shop_id, bool $is_carrier_service_available): void
    {
        $settings = Settings::query()
            ->where('shop_id', $shop_id)
            ->first();

        if ($settings instanceof Settings) {
            $settings->fill([
                'is_carrier_service_available' => $is_carrier_service_available,
            ]);
            if ($settings->isDirty()) {
                $settings->save();
            }
        } else {
            $settings = new Settings([
                'shop_id' => $shop_id,
                'api_key' => '',
                'trading_point_id' => '',
                'order_category' => '',
                'is_carrier_service_available' => $is_carrier_service_available,
                'carrier_service_shipping_rates' => null,
                'shipping_rates' => null,
            ]);
            $settings->save();
        }
    }
}
