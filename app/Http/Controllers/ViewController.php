<?php

namespace App\Http\Controllers;

use App\Models\User;
use Osiset\BasicShopifyAPI\ResponseAccess;

class ViewController extends Controller
{
    public function welcome()
    {
        $shop = auth()->user();

        $selected_user = User::query()
            ->select("*")
            ->where('id', '=', $shop->id)
            ->first();

        $selectable_shipping_rates = [];
        $shopify_shipping_zones = $shop->api()->rest('GET', '/admin/api/2023-07/shipping_zones.json');
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

        if ($selected_user) {
            $post_data = [
                "carrier_service" => [
                    "name" => "QWQER Express",
                    "callback_url" => route('add-rates'),
                    "service_discovery" => "true"
                ],
            ];

            $shop->api()->rest('POST', '/admin/api/2023-07/carrier_services.json', $post_data);
        }

        return view('welcome')
            ->with([
                'selectable_shipping_rates' => $selectable_shipping_rates ?? []
            ]);
    }
}
