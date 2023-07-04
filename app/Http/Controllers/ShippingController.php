<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;

use App\Models\Order_Meta;

use App\Models\Settings;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

class ShippingController extends Controller
{
    public function add_shipping_carrier(Request $request)
    {
    	$post_data =array(
            "carrier_service" => array(
            "name"=>"QWQER Express",
            "callback_url"=>"https://shipping-carrier.chainpulse.tech/add-rates",
            "service_discovery"=>"true"
            ),
        );
        
        $post_carrier = json_encode($post_data);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            
            CURLOPT_URL => 'https://72548a1dc08a895e07bf3d62fe58305b:shpua_ea3954244e43cb76da06e7284ff8047b@shipping-courier.myshopify.com/admin/api/2022-10/carrier_services.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_carrier,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            )
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        return response()->json([ 'success' => true , 'data' => $response ]);
    }

    public function add_shipping_rates(Request $request)
    {
        
        // log the raw request -- this makes debugging much easier
        $filename = time();
        $input = file_get_contents('php://input');
        file_put_contents($filename.'-input', $input);
        
        // parse the request
        $rates = json_decode($input, true);
        
        $address1 = $rates['rate']['origin']['address1'];
        
        $city = $rates['rate']['origin']['city'];
        
        $latitude = $rates['rate']['origin']['latitude'];
        
        $longitude = $rates['rate']['origin']['longitude'];
        
        $address = "$address1 $city";
        
        $d_address1 = $rates['rate']['destination']['address1'];

        $d_city = $rates['rate']['destination']['city'];

        $d_address = "$d_address1 $d_city";

        $settings = Settings::get();

        $api_key= $settings[0]['api_key'];

        $order_category= $settings[0]['order_category'];

        $trading_point_id= $settings[0]['trading_point_id'];

        $post_coordinates = array(
        	"address"=> $d_address
        );


        $post_data_coordinates = json_encode($post_coordinates);
        $curl = curl_init();
        $logged_user_token = $api_key;
        curl_setopt_array($curl, array(
            
            CURLOPT_URL => 'https://qwqer.hostcream.eu/api/v1/places/geocode/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data_coordinates,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer ".$logged_user_token
            )
        ));

        $response_data = curl_exec($curl);
        
        curl_close($curl);
        
        $obj_data = json_decode($response_data,true);

        $d_latitude = $obj_data['data']['coordinates'][0];
        $d_longitude = $obj_data['data']['coordinates'][1];

        
        $post_data =array(
            "type"=>"Regular",
            "real_type"=>"ScheduledDelivery",
            "category"=>$order_category,
            "origin"=>array(
                "address"=> $address1,
                "coordinates"=>array($latitude, $longitude),
            ),
            "destinations"=>array(
                array(
                    "address"=> $d_address1,
                    "coordinates"=>array($d_latitude,$d_longitude),
                    ),
                ),
        );
        
        $post_carrier = json_encode($post_data);
        $curl1 = curl_init();
        $logged_user_token = $api_key;
        curl_setopt_array($curl1, array(
            
            CURLOPT_URL => 'https://qwqer.hostcream.eu/api/v1/clients/auth/trading-points/'.$trading_point_id.'/delivery-orders/get-price',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_carrier,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer ".$logged_user_token
            )
        ));
        
        $response = curl_exec($curl1);
        
        curl_close($curl1);
        
        $obj = json_decode($response,true);
       
        
        $price = $obj['data']['client_price'];
        
        
        
        
     
        // log the array format for easier interpreting
        file_put_contents($filename.'-debug', print_r($rates, true));
        
        
        // build the array of line items using the prior values
        $output = array('rates' => array(
            array(
                'service_name' => 'QWQER Express',
                'service_code' => 'QWQER',
                'total_price' => $price,
                'currency' => 'INR',
                'min_delivery_date' => '',
                'max_delivery_date' => ''
            ),
        ));
        
        // encode into a json response
        $json_output = json_encode($output);
        
        // log it so we can debug the response
        file_put_contents($filename.'-output', $json_output);
        
        // send it back to shopify
        return $json_output;
    }

    public function create_order(Request $request)
    {
        $filename = time();
        
        $input = file_get_contents('php://input');
        
        file_put_contents($filename.'-fulfill', $input);
        
        // parse the request
        $fulfillment = json_decode($input, true);
        
        $data = (array)$fulfillment;
     
        // log the array format for easier interpreting
        file_put_contents($filename.'-order', print_r($data, true));
        
        $order = [];

        $order_id = $data['id'];

        $order['order_id'] = $order_id;

        $email = $data['contact_email'];

        $order['customer_email'] = $email;

        $line_items= $data['line_items'][0];
        
        $address1 = $line_items['origin_location']['address1'];
        
        $city = $line_items['origin_location']['city'];

        $address = "$address1 $city";
                
        $name = $line_items['origin_location']['name'];
        
        $d_address1 = $data['shipping_address']['address1'];

        $d_city = $data['shipping_address']['city'];

        $d_address = "$d_address1 $d_city";

        $order['shipping_address'] = $d_address;

        $d_name = $data['shipping_address']['name'];

        $order['customer_name'] = $d_name;

        $phone = $data['shipping_address']['phone'];

        $b_address1 = $data['billing_address']['address1'];

        $b_city = $data['billing_address']['city'];

        $billing_address = "$b_address1 $b_city";

        $order['billing_address'] = $billing_address;

        $settings = Settings::get();

        $api_key = $settings[0]['api_key'];

        $order_category = $settings[0]['order_category'];

        $trading_point_id = $settings[0]['trading_point_id'];



        $post_origin_coordinates = array(
        	"address"=> $address
        );


        $post_data_origin_coordinates = json_encode($post_origin_coordinates);
        $curl = curl_init();
        $logged_user_token = $api_key;
        curl_setopt_array($curl, array(
            
            CURLOPT_URL => 'https://qwqer.hostcream.eu/api/v1/places/geocode/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data_origin_coordinates,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer ".$logged_user_token
            )
        ));

        $response_data = curl_exec($curl);
        
        curl_close($curl);
        
        $obj_data = json_decode($response_data,true);

        $origin_latitude = $obj_data['data']['coordinates'][0];
        $origin_longitude = $obj_data['data']['coordinates'][1];


        $post_destination_coordinates = array(
        	"address"=> $d_address
        );


        $post_data_destination_coordinates = json_encode($post_destination_coordinates);
        $curl1 = curl_init();
        $logged_user_token = $api_key;
        curl_setopt_array($curl1, array(
            
            CURLOPT_URL => 'https://qwqer.hostcream.eu/api/v1/places/geocode/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data_destination_coordinates,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer ".$logged_user_token
            )
        ));

        $response_data1 = curl_exec($curl1);
        
        curl_close($curl1);
        
        $obj_data1 = json_decode($response_data1,true);

        $destination_latitude = $obj_data1['data']['coordinates'][0];
        $destination_longitude = $obj_data1['data']['coordinates'][1];



        $post_data =array(
            "type"=>"Regular",
            "real_type"=>"ScheduledDelivery",
            "category"=>$order_category,
            "origin"=>array(
                "address"=> $address,
                "coordinates"=>array($origin_latitude,$origin_longitude),
                "name"=> $name,
                "phone" => $phone
            ),
            "destinations"=>array(
                array(
                    "address"=> $d_address,
                    "coordinates"=>array($destination_latitude,$destination_longitude),
                    "name"=> $d_name,
                    "phone" => $phone
                    ),
                ),
        );
        
        $post_carrier = json_encode($post_data);
        $curl2 = curl_init();
        $logged_user_token = $api_key;
        curl_setopt_array($curl2, array(
            
            CURLOPT_URL => 'https://qwqer.hostcream.eu/api/v1/clients/auth/trading-points/'.$trading_point_id.'/delivery-orders',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_carrier,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                "Authorization: Bearer ".$logged_user_token
            )
        ));
        
        $response = curl_exec($curl2);
        
        curl_close($curl2);
        
        $obj = json_decode($response,true);
        
        $order_meta = [];
       
       $order_id = $obj['data']['id'];
       $order_meta['order_id'] = $order_id;
       
       $delivery_area_id = $obj['data']['delivery_area_id'];
       $order_meta['delivery_area_id'] = $delivery_area_id;
       
       $client_id = $obj['data']['client_id'];
       $order_meta['client_id'] = $client_id;
       
       $courier_id = $obj['data']['courier_id'];
       $order_meta['courier_id'] = $courier_id;
       
       $trading_point_id = $obj['data']['trading_point_id'];
       $order_meta['trading_point_id'] = $trading_point_id;
       
       $is_parent = $obj['data']['is_parent'];
       $order_meta['is_parent'] = $is_parent;
       
       $parent_id = $obj['data']['parent_id'];
       $order_meta['parent_id'] = $parent_id;
       
       $status = $obj['data']['status'];
       $order_meta['status'] = $status;
       
       $type = $obj['data']['type'];
       $order_meta['type'] = $type;
       
       $real_type = $obj['data']['real_type'];
       $order_meta['real_type'] = $real_type;
       
       $category = $obj['data']['category'];
       $order_meta['category'] = $category;
       
       $pickup_datetime = $obj['data']['pickup_datetime'];
       $order_meta['pickup_datetime'] = $pickup_datetime;
       
       $is_round_trip = $obj['data']['is_round_trip'];
       $order_meta['is_round_trip'] = $is_round_trip;
       
       $courier_vehicle_id = $obj['data']['courier_vehicle_id'];
       $order_meta['courier_vehicle_id'] = $courier_vehicle_id;
       
       $courier_transport_mode = $obj['data']['courier_transport_mode'];
       $order_meta['courier_transport_mode'] = $courier_transport_mode;
       
       $dropdowns = $obj['data']['dropdowns'];
       $order_meta['dropdowns'] = $dropdowns;
       
       $client_price = $obj['data']['client_price'];
       $order_meta['client_price'] = $client_price;
       
       $client_distance = $obj['data']['client_distance'];
       $order_meta['client_distance'] = $client_distance;
       
       $client_distance_price = $obj['data']['client_distance_price'];
       $order_meta['client_distance_price'] = $client_distance_price;
       
       $client_adjustments_price = $obj['data']['client_adjustments_price'];
       $order_meta['client_adjustments_price'] = $client_adjustments_price;
       
       $client_dropdowns_price = $obj['data']['client_dropdowns_price'];
       $order_meta['client_dropdowns_price'] = $client_dropdowns_price;
       
       $client_pickup_price = $obj['data']['client_pickup_price'];
       $order_meta['client_pickup_price'] = $client_pickup_price;
       
       $direct_distance = $obj['data']['direct_distance'];
       $order_meta['direct_distance'] = $direct_distance;
       
       $distance = $obj['data']['distance'];
       $order_meta['distance'] = $distance;
       
       $accepted_at = $obj['data']['accepted_at'];
       $order_meta['accepted_at'] = $accepted_at;
       
       $picked_up_at = $obj['data']['picked_up_at'];
       $order_meta['picked_up_at'] = $picked_up_at;
       
       $finished_at = $obj['data']['finished_at'];
       $order_meta['finished_at'] = $finished_at;
        
        // return $order_meta;die;
        
       Order_Meta::insert($order);

        Order::insert($order_meta);
        
        return response()->json([ 'success' => true , 'data' => $response ]);
        
    }
}
