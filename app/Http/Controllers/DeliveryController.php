<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DeliveryController extends Controller
{
    public function add_shipping_rates(Request $request): JsonResponse
    {
        $shopName = $request->header('X-Shopify-Shop-Domain');

        $shop = User::query()
            ->where('name', '=', $shopName)
            ->first();

        if (!$shop instanceof User) {
            return response()->json([[
                'success' => false,
                'message' => 'Shop not found!'
            ]], 422);
        }
        $shop_id = $shop->id;

        $settings = Settings::query()
            ->where('shop_id', $shop_id)
            ->first();

        if (!$settings instanceof Settings) {
            return response()->json([[
                'success' => false,
                'message' => 'Settings not found!'
            ]], 422);
        }

        $api_key = $settings->api_key;
        $order_category = $settings->order_category;
        $trading_point_id = $settings->trading_point_id;
        $carrier_service_shipping_rates = $settings->carrier_service_shipping_rates;

        $qwqer_api = $settings->api === 'Live'
            ? config('shopify-app.qwqer_api')
            : config('shopify-app.qwqer_test_api');

        $carriers = [];

        if (!isset($carrier_service_shipping_rates)) {
            return response()->json([
                'rates' => $carriers,
            ]);
        }

        // parse the request
        $rates = $request->all();
        $origin_latitude = $rates['rate']['origin']['latitude'];
        $origin_longitude = $rates['rate']['origin']['longitude'];
        $origin_address1 = $rates['rate']['origin']['address1'];

        $destination_address1 = $rates['rate']['destination']['address1'];
        $destination_city = $rates['rate']['destination']['city'];

        $geocode_response = Http:: withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Bearer $api_key",
        ])
            ->post(
                $qwqer_api . 'places/geocode',
                ['address' => "$destination_address1 $destination_city"]
            );

        $destination_coordinates = json_decode($geocode_response, true);
        // log the response for easier interpreting
//        file_put_contents(public_path('carriers.log'), print_r($destination_coordinates, true), FILE_APPEND);

        if ($geocode_response->status() !== 200) {
            return response()->json([[
                'success' => false,
                'message' => ($destination_coordinates['message'] ?? '') === 'Unauthenticated.'
                    ? 'Invalid API key!'
                    : $destination_coordinates['message'] ?? 'Unknown error!'
            ]], $geocode_response->status());
        }

        $destination_latitude = $destination_coordinates['data']['coordinates'][0];
        $destination_longitude = $destination_coordinates['data']['coordinates'][1];

        foreach ($carrier_service_shipping_rates as $carrier_code) {
            if ($carrier_code === 'QWQER_EVENING') {
                $price_response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer $api_key",
                ])
                    ->post(
                        $qwqer_api . 'clients/auth/trading-points/' . $trading_point_id . '/delivery-orders/get-price',
                        [
                            "type" => "Regular",
                            "real_type" => "ScheduledDelivery",
                            "category" => $order_category,
                            "origin" => [
                                "address" => $origin_address1,
                                "coordinates" => [$origin_latitude, $origin_longitude],
                            ],
                            "destinations" => [
                                [
                                    "address" => $destination_address1,
                                    "coordinates" => [$destination_latitude, $destination_longitude],
                                ],
                            ],
                        ]
                    );

                $client_price = json_decode($price_response, true);
                // log the response for easier interpreting
//                file_put_contents(public_path('carriers.log'), print_r($client_price, true), FILE_APPEND);

                if ($price_response->status() !== 200) {
                    continue;
                }

                $price = $client_price['data']['client_price'];

                $carriers[] = [
                    'service_name' => 'QWQER Same-day (18:00 - 22:00)',
                    'service_code' => $carrier_code,
                    'total_price' => $price,
                    'currency' => 'EUR',
                    'phone_required' => true,
                ];

            } elseif ($carrier_code === 'QWQER_EXPRESS') {
                $price = 4.95 + 0.41 * $this->getDistance(
                        $origin_latitude,
                        $origin_longitude,
                        $destination_latitude,
                        $destination_longitude,
                    );

                $carriers[] = [
                    'service_name' => 'QWQER Express',
                    'service_code' => $carrier_code,
                    'total_price' => round($price, 2) * 100,
                    'currency' => 'EUR',
                    'phone_required' => true,
                ];
            }
        }

        // log the carriers for easier interpreting
//        file_put_contents(public_path('carriers.log'), print_r($carriers, true), FILE_APPEND);

        // send it back to shopify
        return response()->json([
            'rates' => $carriers,
        ]);
    }

    private function getDistance($latitude_from, $longitude_from, $latitude_to, $longitude_to): float
    {
        // convert from degrees to radians
        $lat_from = deg2rad($latitude_from);
        $lat_to = deg2rad($latitude_to);
        $lon_from = deg2rad($longitude_from);
        $lon_to = deg2rad($longitude_to);

        $lat_delta = $lat_to - $lat_from;
        $lon_delta = $lon_to - $lon_from;

        $angle = 2 * asin(sqrt(
                pow(sin($lat_delta / 2), 2)
                + cos($lat_from) * cos($lat_to) * pow(sin($lon_delta / 2), 2)
            ));

        return $angle * 6371; // multiply with Earth radius in km
    }
}
