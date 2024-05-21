<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Settings;

class SettingsController extends Controller
{
    private const VALID_SETTINGS = [
        'api',
        'api_key',
        'trading_point_id',
        'order_category',
        'carrier_service_shipping_rates',
        'shipping_rates',
    ];

    public function update_details(Request $request): JsonResponse
    {
        $shop = auth()->user();
        if (!$shop instanceof User) {
            return response()->json([[
                'success' => false,
                'message' => 'Shop not found!'
            ]], 422);
        }
        $shop_id = $shop->id;

        $input = array_filter(
            $request->all(),
            fn($value, $key) => in_array($key, self::VALID_SETTINGS),
            ARRAY_FILTER_USE_BOTH
        );
        $input = array_merge(array_fill_keys(self::VALID_SETTINGS, ''), $input);
        $input = array_map(function ($value) {
            return (is_null($value)) ? '' : $value;
        }, $input);

        $settings = Settings::query()
            ->where('shop_id', $shop_id)
            ->first();

        if ($settings instanceof Settings) {
            $settings->fill($input);
            if ($settings->isDirty()) {
                $settings->save();
            }
        } else {
            $settings = new Settings(['shop_id' => $shop_id]);
            $settings->fill($input);
            $settings->save();
        }

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function get_details(): JsonResponse
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

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
