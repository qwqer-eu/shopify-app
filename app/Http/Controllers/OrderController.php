<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use App\Models\Order;

use App\Models\Order_Meta;

use App\Models\Location;

use DataTables;

class OrderController extends Controller
{
    public function get_orders(Request $request)
    {
        $sid = $request->input('id');
            // return $sid;die;

        $data = Order_Meta::where('shop_id',$sid)->get(['*']);

        return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
     
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    public function get_delivery_orders(Request $request)
    {

        $s_id = $request->input('id');
            // return $s_id;die;

    	$data = Order::where('shop_id',$s_id)->get(['id', 'shop_id', 'order_id', 'status', 'client_price', 'client_distance', 'distance']);

        return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
     
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }    
}
