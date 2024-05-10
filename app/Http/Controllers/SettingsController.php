<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use App\Models\Settings;

class SettingsController extends Controller
{
    public function update_details(Request $request)
    {
        $shop = auth()->user();
        if (!$shop instanceof User) {
            return response()->json([[
                'success' => false,
                'message' => 'Shop not found!'
            ]], 422);
        }
        $shop_id = $shop->id;

        $input = $request->all([
            'api_key',
            'trading_point_id',
            'order_category',
            'shipping_rates',
        ]);

        $settings = Settings::query()
            ->where('shop_id', $shop_id)
            ->first();

        if ($settings instanceof Settings) {
            $settings->update([
                'api_key' => $input['api_key'],
                'trading_point_id' => $input['trading_point_id'],
                'order_category' => $input['order_category'],
                'shipping_rates' => $input['shipping_rates'],
            ]);
        } else {
            $settings = new Settings([
                'shop_id' => $shop_id,
                'api_key' => $input['api_key'],
                'trading_point_id' => $input['trading_point_id'],
                'order_category' => $input['order_category'],
                'shipping_rates' => $input['shipping_rates'],
            ]);
            $settings->save();
        }

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function get_details()
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
            ->get();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
