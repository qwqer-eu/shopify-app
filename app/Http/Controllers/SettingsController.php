<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Settings;

class SettingsController extends Controller
{
    // public function settings(Request $request)
    // {	
    // 	$id = $request->input('id');

    // 	$api_key = $request->input('api_key');

    // 	$api_url = $request->input('api_url');

    // 	$trading_point_id = $request->input('trading_point_id');

    //     $order_category = $request->input('order_category');

    // 	$settings_order= [];

    // 	$settings_order['api_key'] = $api_key;

    // 	$settings_order['api_url'] = $api_url;

    // 	$settings_order['trading_point_id'] = $trading_point_id;

    //     $settings_order['order_category'] = $order_category;

    //     Settings::truncate($settings_order);

    // 	$response = Settings::insert($settings_order);

    // 	return response()->json([ 'success' => true , 'data' => $response ]);
    // }

    public function get_details(Request $request)
    {
        $data = Settings::get();

        return response()->json(['success' =>true, 'data'=>$data]);
    }

    public function update_details(Request $request)
    {
        $id = $request->input('id');

        $api_key = $request->input('api_key');

        $trading_point_id = $request->input('trading_point_id');

        $order_category = $request->input('order_category');

       	$response = Settings::where('id',$id)->update(['api_key' => $api_key, 'trading_point_id' => $trading_point_id, 'order_category' => $order_category]);

        return response()->json([ 'success' => true , 'data' => $response ]);
    }

}
