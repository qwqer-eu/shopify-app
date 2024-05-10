<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

class DeliveryController extends Controller
{
    public function add_shipping_rates(Request $request)
    {
        $shop = auth()->user();
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

        // log the raw request -- this makes debugging much easier
//        $filename = time();
//        $input = file_get_contents('php://input');
//        file_put_contents($filename . '-input', $input);

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
                config('shopify-app.qwqer_api') . 'places/geocode',
                ['address' => "$destination_address1 $destination_city"]
            );

        $destination_coordinates = json_decode($geocode_response, true);
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

        $price_response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Bearer $api_key",
        ])
            ->post(
                config('shopify-app.qwqer_api') . 'clients/auth/trading-points/' . $trading_point_id . '/delivery-orders/get-price',
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
        if ($price_response->status() !== 200) {
            return response()->json([[
                'success' => false,
                'message' => ($client_price['message'] ?? '') === 'Unauthenticated.'
                    ? 'Invalid API key!'
                    : $client_price['message'] ?? 'Unknown error!'
            ]], $price_response->status());
        }

        $price = $client_price['data']['client_price'];

        // log the array format for easier interpreting
//        file_put_contents($filename . '-debug', print_r($rates, true));

        // build the array of line items using the prior values
        $output = [
            'rates' => [
                [
                    'service_name' => 'QWQER Express',
                    'service_code' => 'QWQER',
                    'total_price' => $price,
                    'currency' => 'INR',
                    'min_delivery_date' => '',
                    'max_delivery_date' => ''
                ],
            ]
        ];

        // log it so we can debug the response
//        file_put_contents($filename . '-output', $json_output);

        // send it back to shopify
        return json_encode($output);
    }
}
