<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DeliveryController extends Controller
{
    private const VALID_POST_CODES = [
        1002, 1003, 1004, 1005, 1006,
        1007, 1009, 1010, 1011, 1012,
        1013, 1014, 1015, 1016, 1019,
        1021, 1024, 1026, 1029, 1030,
        1034, 1035, 1039, 1045, 1046,
        1048, 1050, 1053, 1055, 1057,
        1058, 1063, 1064, 1067, 1069,
        1073, 1076, 1079, 1082, 1083,
        1084, 2101, 2111, 2112, 2167,
    ];

    public function add_shipping_rates(Request $request): JsonResponse
    {
        $shopName = $request->header('X-Shopify-Shop-Domain');

        $rates = $request->all();
        $origin_latitude = $rates['rate']['origin']['latitude'];
        $origin_longitude = $rates['rate']['origin']['longitude'];
        $origin_address1 = $rates['rate']['origin']['address1'];

        $destination_address1 = $rates['rate']['destination']['address1'];
        $destination_city = $rates['rate']['destination']['city'];

        $destination_country = $rates['rate']['destination']['country'];
        $destination_post_code = $rates['rate']['destination']['postal_code'];

        $shop = User::query()
            ->where('name', '=', $shopName)
            ->first();

        if (!$shop instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found!'
            ], 422);
        }
        $shop_id = $shop->id;

        $settings = Settings::query()
            ->where('shop_id', $shop_id)
            ->first();

        if (!$settings instanceof Settings) {
            return response()->json([
                'success' => false,
                'message' => 'Settings not found!'
            ], 422);
        }

        $api_key = $settings->api_key;
        $order_category = $settings->order_category;
        $trading_point_id = $settings->trading_point_id;
        $carrier_service_shipping_rates = $settings->carrier_service_shipping_rates;

        $qwqer_api = $settings->api === 'Live'
            ? config('shopify-app.qwqer_api')
            : config('shopify-app.qwqer_test_api');

        $carriers = [];

        if (
            !is_array($carrier_service_shipping_rates)
            || empty($carrier_service_shipping_rates)
            || $destination_country !== 'LV'
            || !in_array(
                (int)preg_replace('/[^0-9]/', '', $destination_post_code),
                self::VALID_POST_CODES
            )
        ) {
            return response()->json([
                'rates' => [],
            ], 404);
        }

        $trading_point_response = Http:: withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Bearer $api_key",
        ])
            ->get(
                $qwqer_api . 'plugins/shopify/trading-points/' . $trading_point_id,
                ['include' => 'working_hours,merchant']
            );

        $time_now = now()->timezone('Europe/Riga');
        $day_today = $time_now->format('l');

        $trading_point = json_decode($trading_point_response, true);

        if ($trading_point_response->status() !== 200) {
            return response()->json([
                'success' => false,
                'message' => ($trading_point['message'] ?? '') === 'Unauthenticated.'
                    ? 'Invalid API key!'
                    : $trading_point['message'] ?? 'Unknown error!'
            ], $trading_point_response->status());
        }

        if (isset($trading_point['data']['working_hours'])) {
            $all_working_hours = $trading_point['data']['working_hours'];
            $working_hours_today = $this->get_working_hours($all_working_hours, $day_today);

            if (isset($working_hours_today['time_from']) && isset($working_hours_today['time_to'])) {
                [$time_from_hours, $time_from_minutes] = explode(':', $working_hours_today['time_from']);
                [$time_to_hours, $time_to_minutes] = explode(':', $working_hours_today['time_to']);
                $time_from = (clone $time_now)->hours($time_from_hours)->minutes($time_from_minutes);
                $time_to = (clone $time_now)->hours($time_to_hours)->minutes($time_to_minutes);

                if ($time_now->gt($time_from) && $time_now->lt($time_to)) {
                    $description = "Delivery available today from {$working_hours_today['time_from']} to {$working_hours_today['time_to']}";
                } else {
                    $next_available_day = null;
                    $search_day_count = 0;

                    while ($search_day_count < 7) {
                        $time_now = $time_now->addDay();
                        $day_today = $time_now->format('l');

                        $working_hours = $this->get_working_hours($all_working_hours, $day_today);

                        if (isset($working_hours)) {
                            $next_available_day = $time_now;
                            break;
                        }

                        $search_day_count++;
                    }

                    if (
                        isset($next_available_day)
                        && isset($working_hours['time_from'])
                        && isset($working_hours['time_to'])
                    ) {
                        $description = "Next available delivery time is "
                            . $time_now->format('l, F j')
                            . " {$working_hours['time_from']} to {$working_hours['time_to']}";
                    }
                }
            }
        }

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

        if ($geocode_response->status() !== 200) {
            return response()->json([
                'success' => false,
                'message' => ($destination_coordinates['message'] ?? '') === 'Unauthenticated.'
                    ? 'Invalid API key!'
                    : $destination_coordinates['message'] ?? 'Unknown error!'
            ], $geocode_response->status());
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
                    'description' => $description ?? '',
                ];

            } elseif ($carrier_code === 'QWQER_EXPRESS') {
                $price = 4.95 + 0.41 * $this->get_distance(
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
                    'description' => $description ?? '',
                ];
            }
        }

        return response()->json([
            'rates' => $carriers,
        ]);
    }

    private function get_distance($latitude_from, $longitude_from, $latitude_to, $longitude_to): float
    {
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

    private function get_working_hours(array $all_working_hours, string $day): ?array
    {
        $working_hours_key = array_search($day, array_column($all_working_hours, 'day_of_week'));

        if ($working_hours_key !== false && is_array($all_working_hours[$working_hours_key])) {
            return $all_working_hours[$working_hours_key];
        }

        return null;
    }
}
