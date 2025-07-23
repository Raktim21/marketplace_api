<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Tenant;
use App\Models\UserRating;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckReview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-review';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = Tenant::get();
  

        foreach ($tenants as $key => $tenant) {

            tenancy()->initialize($tenant);

            $orders = Order::where('payment_status', 1)->with('review')->get();

            foreach ($orders as $key => $order) {

                if ($order->review == null) {
     
                    if (Carbon::parse($order->created_at)->diffInDays(Carbon::now()) > 7) {

                        UserRating::create([
                            'order_id'   => $order->id,
                            'rating'     => 5,
                            'review'     => 'Automatic feedback',
                            'name'       => $order->name,
                            'contact'    => $order->email,
                        ]);
                    }
                }
            }

        }
    }
}
