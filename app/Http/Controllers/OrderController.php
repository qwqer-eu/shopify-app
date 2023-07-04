<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Order_Meta;
use DataTables;

class OrderController extends Controller
{
    public function get_orders(Request $request)
    {
        $data = Order_Meta::get(['*']);

        return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
     
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    public function get_delivery_orders(Request $request)
    {
    	$data = Order::get(['id', 'order_id', 'status', 'client_price', 'client_distance', 'distance']);

        return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
     
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

}
