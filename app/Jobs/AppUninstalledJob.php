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
use App\Models\Settings;
use App\Models\Location;
use Log;

class AppUninstalledJob implements ShouldQueue
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
		$this->app_uninstalled_job($this->shopDomain->toNative() );

        // Do what you wish with the data
        // Access domain name as $this->shopDomain->toNative()
    }
	
	 public function app_uninstalled_job($shop) {

        try {

            $shop = User::where('name',$shop)->first();
            info($shop);            
            
            //User::where('name',$this->shopDomain->toNative())->update(['plan_id' => null]);
			Order::where('shop_id',$shop->id)->delete();
			Order_Meta::where('shop_id',$shop->id)->delete();
			Settings::where('shop_id',$shop->id)->delete();
			            
            $shop->delete();        
              
        }
        
        catch(\Exception $e) {
            
            Log::error($e->getMessage());
            
        }

    }
}
