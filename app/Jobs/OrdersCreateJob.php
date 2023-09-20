<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;

use App\Models\User;

use App\Models\Order;

use App\Models\Order_Meta;

class OrdersCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain|string
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string   $shopDomain The shop's myshopify domain.
     * @param stdClass $data       The webhook data (JSON decoded).
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Convert domain
        $this->shopDomain = ShopDomain::fromNative($this->shopDomain);

        $this->create_order($this->shopDomain->toNative());

        // Do what you wish with the data
        // Access domain name as $this->shopDomain->toNative()
    }

    public function create_order($shopDomain)
    {
        try {

        $shop  = User::where('name',$shopDomain)->first();
        $user_id =   $shop->id;

        return$user_id;die;

        }

         catch(\Exception $e) {
            Log::error($e->getMessage());
        }


        // $webhook_order_id  =  file_get_contents('php://input');
        // $get_order_data  =  json_decode($webhook_order_id,true);
        
        // info($get_order_data);
       //  $filename = time();
        
       //  $input = file_get_contents('php://input');
        
       //  file_put_contents($filename.'-fulfill', $input);
        
       //  // parse the request
       //  $fulfillment = json_decode($input, true);
        
       //  $data = (array)$fulfillment;
     
       //  // log the array format for easier interpreting
       //  file_put_contents($filename.'-order', print_r($data, true));
        
       //  $order = [];
        
       //  $order['user_id'] = $user_id;
        
       //  $order_id = $data['id'];
        
       //  $order['order_id'] = $order_id;
        
       //  $email = $data['contact_email'];
        
       //  $order['customer_email'] = $email;
        
       //  $line_items= $data['line_items'][0];
        
       //  $address1 = $line_items['origin_location']['address1'];
        
       //  $city = $line_items['origin_location']['city'];
        
       //  $address = "$address1 $city";
                
       //  $name = $line_items['origin_location']['name'];
        
       //  $d_address1 = $data['shipping_address']['address1'];
        
       //  $d_city = $data['shipping_address']['city'];
        
       //  $d_address = "$d_address1 $d_city";
        
       //  $order['shipping_address'] = $d_address;
        
       //  $d_name = $data['shipping_address']['name'];
        
       //  $order['customer_name'] = $d_name;
        
       //  $phone = $data['shipping_address']['phone'];
        
       //  $b_address1 = $data['billing_address']['address1'];
        
       //  $b_city = $data['billing_address']['city'];
        
       //  $billing_address = "$b_address1 $b_city";
        
       //  $order['billing_address'] = $billing_address;
        
       //  // $settings = Settings::get();
        
       //  // $api_key = $settings[0]['api_key'];
        
       //  // $order_category = $settings[0]['order_category'];
        
       //  // $trading_point_id = $settings[0]['trading_point_id'];
        
       //  $post_origin_coordinates = array(
       //      "address"=> $address
       //  );
        
       //  $post_data_origin_coordinates = json_encode($post_origin_coordinates);
       //  $curl = curl_init();
       //  $logged_user_token = "VWn17MYTcMA4SOsoHh5FKdDDa6S4ZFKYaqQZBeq1";
       //  curl_setopt_array($curl, array(
            
       //      CURLOPT_URL => 'https://qwqer.hostcream.eu/api/v1/places/geocode/',
       //      CURLOPT_RETURNTRANSFER => true,
       //      CURLOPT_ENCODING => '',
       //      CURLOPT_MAXREDIRS => 10,
       //      CURLOPT_TIMEOUT => 0,
       //      CURLOPT_FOLLOWLOCATION => true,
       //      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       //      CURLOPT_CUSTOMREQUEST => 'POST',
       //      CURLOPT_POSTFIELDS => $post_data_origin_coordinates,
       //      CURLOPT_HTTPHEADER => array(
       //          'Content-Type: application/json',
       //          'Accept: application/json',
       //          "Authorization: Bearer ".$logged_user_token
       //      )
       //  ));
        
       //  $response_data = curl_exec($curl);
        
       //  curl_close($curl);
        
       //  $obj_data = json_decode($response_data,true);
        
       //  $origin_latitude = $obj_data['data']['coordinates'][0];
       //  $origin_longitude = $obj_data['data']['coordinates'][1];
        
       //  $post_destination_coordinates = array(
       //      "address"=> $d_address
       //  );
        
       //  $post_data_destination_coordinates = json_encode($post_destination_coordinates);
       //  $curl1 = curl_init();
       //  $logged_user_token = "VWn17MYTcMA4SOsoHh5FKdDDa6S4ZFKYaqQZBeq1";
       //  curl_setopt_array($curl1, array(
            
       //      CURLOPT_URL => 'https://qwqer.hostcream.eu/api/v1/places/geocode/',
       //      CURLOPT_RETURNTRANSFER => true,
       //      CURLOPT_ENCODING => '',
       //      CURLOPT_MAXREDIRS => 10,
       //      CURLOPT_TIMEOUT => 0,
       //      CURLOPT_FOLLOWLOCATION => true,
       //      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       //      CURLOPT_CUSTOMREQUEST => 'POST',
       //      CURLOPT_POSTFIELDS => $post_data_destination_coordinates,
       //      CURLOPT_HTTPHEADER => array(
       //          'Content-Type: application/json',
       //          'Accept: application/json',
       //          "Authorization: Bearer ".$logged_user_token
       //      )
       //  ));
        
       //  $response_data1 = curl_exec($curl1);
        
       //  curl_close($curl1);
        
       //  $obj_data1 = json_decode($response_data1,true);
        
       //  $destination_latitude = $obj_data1['data']['coordinates'][0];
       //  $destination_longitude = $obj_data1['data']['coordinates'][1];
        
       //  $post_data =array(
       //      "type"=>"Regular",
       //      "real_type"=>"ScheduledDelivery",
       //      "category"=>"Food",
       //      "origin"=>array(
       //          "address"=> $address,
       //          "coordinates"=>array($origin_latitude,$origin_longitude),
       //          "name"=> $name,
       //          "phone" => $phone
       //      ),
       //      "destinations"=>array(
       //          array(
       //              "address"=> $d_address,
       //              "coordinates"=>array($destination_latitude,$destination_longitude),
       //              "name"=> $d_name,
       //              "phone" => $phone
       //              ),
       //          ),
       //  );
        
       //  $post_carrier = json_encode($post_data);
       //  $curl2 = curl_init();
       //  $logged_user_token = "VWn17MYTcMA4SOsoHh5FKdDDa6S4ZFKYaqQZBeq1";
       //  curl_setopt_array($curl2, array(
            
       //      CURLOPT_URL => 'https://qwqer.hostcream.eu/api/v1/clients/auth/trading-points/3/delivery-orders',
       //      CURLOPT_RETURNTRANSFER => true,
       //      CURLOPT_ENCODING => '',
       //      CURLOPT_MAXREDIRS => 10,
       //      CURLOPT_TIMEOUT => 0,
       //      CURLOPT_FOLLOWLOCATION => true,
       //      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       //      CURLOPT_CUSTOMREQUEST => 'POST',
       //      CURLOPT_POSTFIELDS => $post_carrier,
       //      CURLOPT_HTTPHEADER => array(
       //          'Content-Type: application/json',
       //          'Accept: application/json',
       //          "Authorization: Bearer ".$logged_user_token
       //      )
       //  ));
        
       //  $response = curl_exec($curl2);
        
       //  curl_close($curl2);
        
       //  $obj = json_decode($response,true);
        
       //  $order_meta = [];
        
       //  $order_meta['user_id'] = $user_id;
       
       // $order_id = $obj['data']['id'];
       // $order_meta['order_id'] = $order_id;
       
       // $delivery_area_id = $obj['data']['delivery_area_id'];
       // $order_meta['delivery_area_id'] = $delivery_area_id;
       
       // $client_id = $obj['data']['client_id'];
       // $order_meta['client_id'] = $client_id;
       
       // $courier_id = $obj['data']['courier_id'];
       // $order_meta['courier_id'] = $courier_id;
       
       // $trading_point_id = $obj['data']['trading_point_id'];
       // $order_meta['trading_point_id'] = $trading_point_id;
       
       // $is_parent = $obj['data']['is_parent'];
       // $order_meta['is_parent'] = $is_parent;
       
       // $parent_id = $obj['data']['parent_id'];
       // $order_meta['parent_id'] = $parent_id;
       
       // $status = $obj['data']['status'];
       // $order_meta['status'] = $status;
       
       // $type = $obj['data']['type'];
       // $order_meta['type'] = $type;
       
       // $real_type = $obj['data']['real_type'];
       // $order_meta['real_type'] = $real_type;
       
       // $category = $obj['data']['category'];
       // $order_meta['category'] = $category;
       
       // $pickup_datetime = $obj['data']['pickup_datetime'];
       // $order_meta['pickup_datetime'] = $pickup_datetime;
       
       // $is_round_trip = $obj['data']['is_round_trip'];
       // $order_meta['is_round_trip'] = $is_round_trip;
       
       // $courier_vehicle_id = $obj['data']['courier_vehicle_id'];
       // $order_meta['courier_vehicle_id'] = $courier_vehicle_id;
       
       // $courier_transport_mode = $obj['data']['courier_transport_mode'];
       // $order_meta['courier_transport_mode'] = $courier_transport_mode;
       
       // $dropdowns = $obj['data']['dropdowns'];
       // $order_meta['dropdowns'] = $dropdowns;
       
       // $client_price = $obj['data']['client_price'];
       // $order_meta['client_price'] = $client_price;
       
       // $client_distance = $obj['data']['client_distance'];
       // $order_meta['client_distance'] = $client_distance;
       
       // $client_distance_price = $obj['data']['client_distance_price'];
       // $order_meta['client_distance_price'] = $client_distance_price;
       
       // $client_adjustments_price = $obj['data']['client_adjustments_price'];
       // $order_meta['client_adjustments_price'] = $client_adjustments_price;
       
       // $client_dropdowns_price = $obj['data']['client_dropdowns_price'];
       // $order_meta['client_dropdowns_price'] = $client_dropdowns_price;
       
       // $client_pickup_price = $obj['data']['client_pickup_price'];
       // $order_meta['client_pickup_price'] = $client_pickup_price;
       
       // $direct_distance = $obj['data']['direct_distance'];
       // $order_meta['direct_distance'] = $direct_distance;
       
       // $distance = $obj['data']['distance'];
       // $order_meta['distance'] = $distance;
       
       // $accepted_at = $obj['data']['accepted_at'];
       // $order_meta['accepted_at'] = $accepted_at;
       
       // $picked_up_at = $obj['data']['picked_up_at'];
       // $order_meta['picked_up_at'] = $picked_up_at;
       
       // $finished_at = $obj['data']['finished_at'];
       // $order_meta['finished_at'] = $finished_at;
        
       //  // return $order_meta;die;
        
       // Order_Meta::insert($order);
        
       //  Order::insert($order_meta);
        
       //  return response()->json([ 'success' => true , 'data' => $response ]);

    }
}
