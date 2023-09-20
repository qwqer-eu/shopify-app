<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DeliveryController;

use App\Http\Controllers\OrderController;

use App\Http\Controllers\SettingsController;

use App\Models\User;

use App\Models\Location;

use App\Models\Order_Meta;

use App\Models\Order;

use App\Models\Settings;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
     $shop =auth()->user();
     
        $user_id = $shop->id;
        
        $password = $shop->password;
        
        $name = $shop->name;
        
        $selected_user = User::select("*")->where('id',$user_id)->first();
     
        
        if ($selected_user)
        {
        
        $post_data =array(
            "carrier_service" => array(
            "name"=>"QWQER Express",
            "callback_url"=>"https://shipping-express.chainpulse.tech/add-rates",
            "service_discovery"=>"true"
            ),
        );
     
        // $post_carrier = json_encode($post_data);
                
        $create_products = $shop->api()->rest('POST','/admin/api/2022-10/carrier_services.json',$post_data);
        
        }
     
     
        if ($user_id)
        {
            $location = $shop->api()->rest('GET','/admin/api/2023-07/locations.json');
            
            $locations = $location['body']['locations'];
            
            for($i=0; $i<count($locations); $i++)
            {
                
                $origin_location=[];
                
                $location_id = $locations[$i]['id'];
                
                $origin_location['location_id'] = $location_id;
                
                $address = $locations[$i]['address1'];
                
                $origin_location['address'] = $address;
                
                $origin_name = $locations[$i]['name'];
                
                $origin_location['name'] = $origin_name;
                
                $city = $locations[$i]['city'];
                
                $origin_location['city'] = $city;
                
                $org_location = "$address $city";
                
                $origin_phone = $locations[$i]['phone'];
                
                $cleanedString = preg_replace('/\D/', '', $origin_phone);
                
                if (strlen($cleanedString) >= 10) {
    $formattedNumber = '+' . substr($cleanedString, 0, 3) . ' ' . substr($cleanedString, 3, 2) . ' ' . substr($cleanedString, 5, 3) . ' ' . substr($cleanedString, 8, 3);
}
                
                $origin_location['phone'] = $formattedNumber;
                
                
                $location_count = Location::where('location_id', $location_id)->count();
                
                if ($location_count == 0)
                {
                    Location::insert($origin_location);
                }
                
            }
        }
            
            // $shop_user_id = User::get('id');
            
            
         
            $orders_data = $shop->api()->rest('GET','/admin/api/2022-10/orders.json');
            
            $orders = $orders_data['body']['orders'];
            
            
            
            for($i=0; $i<count($orders); $i++)
            {
               
                
                $order = [];
                
                $order_id = $orders[$i]['id'];  
                
                $order['order_id'] = $order_id;
                
                $order['shop_id'] = $user_id;
                
                $d_address1 = $orders[$i]['shipping_address']['address1'];
                
                $d_city = $orders[$i]['shipping_address']['city'];
                
                $d_address = "$d_address1 $d_city";
                
                $order['shipping_address'] = $d_address;
                
                $d_name = $orders[$i]['shipping_address']['name'];
                
                $order['customer_name'] = $d_name;
                
                $d_phone = $orders[$i]['shipping_address']['phone'];
                // return $d_phone;die;  
                $order['customer_phone'] = $d_phone;
                
                $b_address1 = $orders[$i]['billing_address']['address1'];
                
                $b_city = $orders[$i]['billing_address']['city'];
                
                $billing_address = "$b_address1 $b_city";
                
                $order['billing_address'] = $billing_address;
                
                $order_count = Order_Meta::where('order_id', $order_id)->count();
                
               
                if ($order_count == 0)
                {
                   Order_Meta::insert($order);


                   $settings_table = Settings::where('shop_id',$user_id)->get();

                    
                        $api_key= $settings_table[0]['api_key'];

                        $order_category= $settings_table[0]['order_category'];

                        $trading_point_id= $settings_table[0]['trading_point_id'];
            
                    

                $post_coordinates = array(
                	"address"=> $org_location
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
                
                // $shop_order = Order_Meta::where('order_id', $order_id)->count();
                // if ($order_count == 0)
                // {
                
                    $post_data =array(
                        "type"=>"Regular",
                        "real_type"=>"ScheduledDelivery",
                        "category"=>$order_category,
                        "origin"=>array(
                            "address"=> $org_location,
                            "coordinates"=>array($origin_latitude,$origin_longitude),
                            "name"=> $origin_name,
                            "phone" => $formattedNumber
                        ),
                        "destinations"=>array(
                            array(
                                "address"=> $d_address,
                                "coordinates"=>array($destination_latitude,$destination_longitude),
                                "name"=> $d_name,
                                "phone" => $d_phone
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
                    // return $obj;die;
                    $order_meta = [];
                    
                    $order_meta['shop_id'] = $user_id;
                   
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
                    
                    Order::insert($order_meta);
                // }
                }	
                                  
            }
           
            
        
        

    return view('welcome');
})->middleware(['verify.shopify'])->name('home');

// Route::post('create-carrier', [DeliveryController::class, 'add_shipping_carrier']);
Route::post('add-rates', [DeliveryController::class, 'add_shipping_rates']);

Route::post('create-order', [DeliveryController::class, 'create_order']);

Route::get('order-details', [OrderController::class, 'get_orders']);
 
Route::get('get-delivery-orders', [OrderController::class, 'get_delivery_orders']);

Route::post('update-api-details', [SettingsController::class, 'update_details']);


Route::get('api-details', [SettingsController::class, 'get_details']);