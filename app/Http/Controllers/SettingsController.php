<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Settings;

class SettingsController extends Controller
{
    public function update_details(Request $request)
    {
        $id = $request->input('id');

        $api_key = $request->input('api_key');

        $trading_point_id = $request->input('trading_point_id'); 

        $order_category = $request->input('order_category');

        $shop_id = $request->input('shop_id');

        $settings_order= [];

    	$settings_order['api_key'] = $api_key;

    	$settings_order['shop_id'] = $shop_id;

    	$settings_order['trading_point_id'] = $trading_point_id;

        $settings_order['order_category'] = $order_category;

        $set = Settings::where('shop_id',$shop_id)->count();

        if($set == 0)
        {
        	$response = Settings::insert($settings_order);
        }

    	else{

       	$response = Settings::where('shop_id',$shop_id)->update(['api_key' => $api_key, 'trading_point_id' => $trading_point_id, 'order_category' => $order_category  ]);
       	}

        return response()->json([ 'success' => true , 'data' => $response ]);
    }

    public function get_details(Request $request)
    {
    	$id = $request->input('shop');
        $data = Settings::where('shop_id',$id)->get();

        return response()->json(['success' =>true, 'data'=>$data]);
    }
}
