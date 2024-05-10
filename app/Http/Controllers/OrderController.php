<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Order_Meta;
use App\Models\Location;
use App\Models\Settings;
use Illuminate\Support\Facades\Http;
use Osiset\BasicShopifyAPI\ResponseAccess;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    public function location_create()
    {
        $shop = auth()->user();
        if (!$shop instanceof User) {
            return response()->json([[
                'success' => false,
                'message' => 'Shop not found!'
            ]], 422);
        }
        $shop_id = $shop->id;

        $shopify_locations_response = $shop->api()->rest('GET', '/admin/api/2023-07/locations.json');

        $shopify_locations = [];
        if (isset($shopify_locations_response['body']['locations'])) {
            if ($shopify_locations_response['body']['locations'] instanceof ResponseAccess) {
                $shopify_locations = $shopify_locations_response['body']['locations']->toArray();
            } elseif (is_array($shopify_locations_response['body']['locations'])) {
                $shopify_locations = $shopify_locations_response['body']['locations'];
            }
        }

        foreach ($shopify_locations as $shopify_location) {
            $phone = preg_replace('/\D/', '', $shopify_location['phone']);

            if (strlen($phone) >= 10) {
                $formattedPhone =
                    '+'
                    . substr($phone, 0, 3)
                    . ' '
                    . substr($phone, 3, 2)
                    . ' '
                    . substr($phone, 5, 3)
                    . ' '
                    . substr($phone, 8, 3);
            }

            $origin_location = [
                'shop_id' => $shop_id,
                'location_id' => $shopify_location['id'],
                'address' => $shopify_location['address1'],
                'name' => $shopify_location['name'],
                'city' => $shopify_location['city'],
                'country' => $shopify_location['country_name'],
                'phone' => $formattedPhone ?? $phone ?? '',
            ];

            $location = Location::query()
                ->where('location_id', $shopify_location['id'])
                ->first();

            if ($shopify_location['address1'] != '') {
                if ($location instanceof Location) {
                    $location->fill($origin_location);
                    if ($location->isDirty()) {
                        $location->save();
                    }
                } else {
                    (new Location($origin_location))->save();
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function get_orders()
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

        if (!isset($settings) || !$settings instanceof Settings) {
            return response()->json([[
                'success' => false,
                'message' => 'Settings not found!'
            ]], 422);
        }

        $qwqer_shipping_rates = isset($settings->shipping_rates)
            ? array_merge($settings->shipping_rates, ['QWQER'])
            : ['QWQER'];

        $orders_data = $shop->api()
            ->rest('GET', '/admin/api/2023-07/orders.json', ['status' => 'open']);

        $shopify_orders = [];
        if (isset($orders_data['body']['orders'])) {
            if ($orders_data['body']['orders'] instanceof ResponseAccess) {
                $shopify_orders = $orders_data['body']['orders']->toArray();
            } elseif (is_array($orders_data['body']['orders'])) {
                $shopify_orders = $orders_data['body']['orders'];
            }
        }

        foreach ($shopify_orders as $shopify_order) {
            $order = [
                'shop_id' => $shop_id,
                'order_id' => $shopify_order['id'],
                'shipping_address' => "{$shopify_order['shipping_address']['address1']} {$shopify_order['shipping_address']['city']}",
                'customer_name' => $shopify_order['shipping_address']['name'],
                'customer_phone' => $shopify_order['shipping_address']['phone'],
                'billing_address' => "{$shopify_order['billing_address']['address1']} {$shopify_order['billing_address']['city']}",
            ];

            $qwqer_exist = false;
            foreach ($shopify_order['shipping_lines'] as $shipping_line) {
                if (in_array($shipping_line['code'], $qwqer_shipping_rates)) {
                    $qwqer_exist = true;
                    break;
                }
            }

            if ($qwqer_exist) {
                $orderMeta = Order_Meta::query()
                    ->where('order_id', $shopify_order['id'])
                    ->where('shop_id', $shop_id)
                    ->first();

                if ($orderMeta instanceof Order_Meta) {
                    $orderMeta->fill($order);
                    if ($orderMeta->isDirty()) {
                        $orderMeta->save();
                    }
                } else {
                    (new Order_Meta($order))->save();
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function get_order_details()
    {
        $shop = auth()->user();
        if (!$shop instanceof User) {
            return response()->json([[
                'success' => false,
                'message' => 'Shop not found!'
            ]], 422);
        }
        $shop_id = $shop->id;

        $data = Order_Meta::query()
            ->where([
                'shop_id' => $shop_id,
                'status' => 0,
            ])
            ->get([
                'id',
                'order_id',
                'customer_name',
                'shipping_address',
                'billing_address'
            ]);

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('checkboxes', function ($row) {
                return '<input type="checkbox" name="order_list" value="' . $row->order_id . '">';
            })
            ->rawColumns(['checkboxes'])
            ->make(true);
    }

    public function process_order(Request $request)
    {
        $shop = auth()->user();
        if (!$shop instanceof User) {
            return response()->json([[
                'success' => false,
                'message' => 'Shop not found!'
            ]], 422);
        }
        $shop_id = $shop->id;
        $order_id = $request->input('order_id');

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

        $location = Location::query()
            ->where('shop_id', $shop_id)
            ->where('country', 'Latvia')
            ->first();

        if (!$location instanceof Location) {
            return response()->json([[
                'success' => false,
                'message' => 'Shop location not found!'
            ]], 422);
        }

        $origin_name = $location->name;
        $origin_phone = $location->phone;

        $origin_location = "$location->address $location->city";

        $order_meta = Order_Meta::query()
            ->where('shop_id', $shop_id)
            ->where('order_id', $order_id)
            ->first();

        if (!$order_meta instanceof Order_Meta) {
            return response()->json([[
                'success' => false,
                'message' => 'Order not found!'
            ]], 422);
        }

        $destination_address = $order_meta['shipping_address'];
        $destination_name = $order_meta['customer_name'];
        $destination_phone = $order_meta['customer_phone'];

        $origin_geocode_response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Bearer $api_key",
        ])
            ->post(
                config('shopify-app.qwqer_api') . 'plugins/shopify/places/geocode',
                ['address' => $origin_location]
            );

        $origin_geocode = json_decode($origin_geocode_response, true);
        if ($origin_geocode_response->status() !== 200) {
            return response()->json([[
                'success' => false,
                'message' => ($origin_geocode['message'] ?? '') === 'Unauthenticated.'
                    ? 'Invalid API key!'
                    : $origin_geocode['message'] ?? 'Unknown error!'
            ]], $origin_geocode_response->status());
        }

        $origin_latitude = $origin_geocode['data']['coordinates'][0];
        $origin_longitude = $origin_geocode['data']['coordinates'][1];


        $destination_geocode_response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Bearer $api_key",
        ])
            ->post(
                config('shopify-app.qwqer_api') . 'plugins/shopify/places/geocode',
                ['address' => $destination_address]
            );

        $destination_geocode = json_decode($destination_geocode_response, true);
        if ($destination_geocode_response->status() !== 200) {
            return response()->json([[
                'success' => false,
                'message' => ($destination_geocode['message'] ?? '') === 'Unauthenticated.'
                    ? 'Invalid API key!'
                    : $destination_geocode['message'] ?? 'Unknown error!'
            ]], $destination_geocode_response->status());
        }

        $destination_latitude = $destination_geocode['data']['coordinates'][0];
        $destination_longitude = $destination_geocode['data']['coordinates'][1];

        $delivery_order_response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Bearer $api_key",
        ])
            ->post(
                config('shopify-app.qwqer_api') . 'plugins/shopify/clients/auth/trading-points/' . $trading_point_id . '/delivery-orders',
                [
                    'type' => 'Regular',
                    'real_type' => 'ScheduledDelivery',
                    'category' => $order_category,
                    'origin' => [
                        'address' => $origin_location,
                        'coordinates' => [$origin_latitude, $origin_longitude],
                        'name' => $origin_name,
                        'phone' => $origin_phone
                    ],
                    'destinations' => [
                        [
                            'address' => $destination_address,
                            'coordinates' => [$destination_latitude, $destination_longitude],
                            'name' => $destination_name,
                            'phone' => $destination_phone
                        ],
                    ],
                ]
            );

        $delivery_order = json_decode($delivery_order_response, true);
        if ($delivery_order_response->status() !== 200) {
            return response()->json([[
                'success' => false,
                'message' => ($delivery_order['message'] ?? '') === 'Unauthenticated.'
                    ? 'Invalid API key!'
                    : $delivery_order['message'] ?? 'Unknown error!'
            ]], $delivery_order_response->status());
        }

        try {
            (new Order([
                'shop_id' => $shop_id,
                'shop_order_id' => $order_id,
                'order_id' => $delivery_order['data']['id'],
                'delivery_area_id' => $delivery_order['data']['delivery_area_id'],
                'client_id' => $delivery_order['data']['client_id'],
                'courier_id' => $delivery_order['data']['courier_id'],
                'trading_point_id' => $delivery_order['data']['trading_point_id'],
                'is_parent' => $delivery_order['data']['is_parent'],
                'parent_id' => $delivery_order['data']['parent_id'],
                'status' => $delivery_order['data']['status'],
                'type' => $delivery_order['data']['type'],
                'real_type' => $delivery_order['data']['real_type'],
                'category' => $delivery_order['data']['category'],
                'pickup_datetime' => $delivery_order['data']['pickup_datetime'],
                'is_round_trip' => $delivery_order['data']['is_round_trip'],
                'courier_vehicle_id' => $delivery_order['data']['courier_vehicle_id'],
                'courier_transport_mode' => $delivery_order['data']['courier_transport_mode'],
                'dropdowns' => $delivery_order['data']['dropdowns'],
                'client_price' => $delivery_order['data']['client_price'],
                'client_distance' => $delivery_order['data']['client_distance'],
                'client_distance_price' => $delivery_order['data']['client_distance_price'],
                'client_adjustments_price' => $delivery_order['data']['client_adjustments_price'],
                'client_dropdowns_price' => $delivery_order['data']['client_dropdowns_price'],
                'client_pickup_price' => $delivery_order['data']['client_pickup_price'],
                'direct_distance' => $delivery_order['data']['direct_distance'],
                'distance' => $delivery_order['data']['distance'],
                'accepted_at' => $delivery_order['data']['accepted_at'],
                'picked_up_at' => $delivery_order['data']['picked_up_at'],
                'finished_at' => $delivery_order['data']['finished_at'],
            ]))->save();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save the order details!'
            ], 500);
        }

        Order_Meta::query()
            ->where('order_id', $order_id)
            ->update(['status' => 1]);

        return response()->json(['success' => true]);
    }

    public function get_delivery_orders()
    {
        $shop = auth()->user();
        if (!$shop instanceof User) {
            return response()->json([[
                'success' => false,
                'message' => 'Shop not found!'
            ]], 422);
        }
        $shop_id = $shop->id;

        $data = Order::query()
            ->where('shop_id', $shop_id)
            ->get(['id', 'shop_order_id', 'order_id', 'status', 'client_price', 'client_distance', 'distance']);

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function privacy_policy()
    {
        return view('privacy-policy');
    }
}
