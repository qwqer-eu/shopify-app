<?php namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;
use App\Models\User;
use App\Models\Order;
use App\Models\Order_Meta;
use App\Models\Settings;

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
     * @param string $shopDomain The shop's myshopify domain.
     * @param stdClass $data The webhook data (JSON decoded).
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
    public function handle(): void
    {
        // Convert domain
        $this->shopDomain = ShopDomain::fromNative($this->shopDomain);
		$this->app_uninstalled_job($this->shopDomain->toNative() );

        // Do what you wish with the data
        // Access domain name as $this->shopDomain->toNative()
    }

    public function app_uninstalled_job($shop): void
    {
        try {
            $shop = User::query()
                ->where('name', $shop)
                ->first();

            info($shop);

            $carrier_services = $shop->api()->rest('GET', '/admin/api/2024-04/carrier_services.json');
            if (isset($carrier_services['body']->container['carrier_services'])) {
                foreach ($carrier_services['body']->container['carrier_services'] as $carrier_service) {
                    if (
                        isset($carrier_service['id'])
                        && isset($carrier_service['name'])
                        && str_contains($carrier_service['name'], 'QWQER')
                    ) {
                        $shop->api()->rest('DELETE', "/admin/api/2024-04/carrier_services/{$carrier_service['id']}.json");
                    }
                }
            }

            Order::query()
                ->where('shop_id', $shop->id)
                ->delete();
            Order_Meta::query()
                ->where('shop_id', $shop->id)
                ->delete();
            Settings::query()
                ->where('shop_id', $shop->id)
                ->delete();

            $shop->delete();

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
