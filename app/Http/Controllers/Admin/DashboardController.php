<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Visitor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if ((request()->has('from') && request()->from != '') && (request()->has('to') && request()->to != '')) {


            $start_time = Carbon::parse(request()->from);
            $end_time   = Carbon::parse(request()->to);

            $total_seller = Seller::whereBetween('created_at', [$start_time, $end_time])->count();

            $total_sell = 0;
            $total_sell_amount = 0;
            $date_sale = [];
            $date = [];

            foreach (Seller::get() as $seller) {
                $total_sell        += DB::table('tenant'.$seller->subdomain.'.orders')->where('payment_status',1)->whereBetween('created_at', [$start_time, $end_time])->count();
                $total_sell_amount += DB::table('tenant'.$seller->subdomain.'.orders')->where('payment_status',1)->whereBetween('created_at', [$start_time, $end_time])->sum('total');
            }

            $period = CarbonPeriod::create($start_time, $end_time);

            foreach ($period as $day) {
                $sell = 0;

                $dayStart = $day->copy()->startOfDay();
                $dayEnd   = $day->copy()->endOfDay();

                foreach (Seller::get() as $seller) {
                    $table = 'tenant' . $seller->subdomain . '.orders';

                    $sell += DB::table($table)
                        ->where('payment_status', 1)
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->sum('total');
                }

                $date_sale[] = $sell;
                $date[]      = $day->format('Y-m-d');
            }

      
        }else{



            $total_seller = Seller::count();

            $total_sell = 0;
            $total_sell_amount = 0;
            $date_sale = [];
            $date = [];

            foreach (Seller::get() as $seller) {
                $total_sell        += DB::table('tenant'.$seller->subdomain.'.orders')->where('payment_status', 1)->count();
                $total_sell_amount += DB::table('tenant'.$seller->subdomain.'.orders')->where('payment_status', 1)->sum('total');
            }

            for ($i=12; $i >= 0; $i--) {
                $sell = 0;
                foreach (Seller::get() as $seller) {

                    $sell += DB::table('tenant'.$seller->subdomain.'.orders')->where('payment_status', 1)->whereYear('created_at', Carbon::now()->subMonths($i)->format('Y'))->whereMonth('created_at', Carbon::now()->subMonths($i)->format('m'))->sum('total');
                }

                $date_sale[] =  $sell; ;
                $date[] = Carbon::now()->subMonths($i)->startOf('month')->format('M, Y');
            }

            
        }


        $latest_sellers = Seller::latest()->take(5)->get();

        // return view('center.dashboard', compact('total_seller','total_sell','total_sell_amount','monthly_sell','months','latest_sellers'));

        return response()->json([
            'status' => true,
            'total_seller' => $total_seller,
            'total_sell' => $total_sell,
            'total_sell_amount' => $total_sell_amount,
            'date_sales' => [
                'data'   => $date_sale,
                'date'   => $date,
            ],
            'latest_sellers' => $latest_sellers
        ]);
    }

    public function analytics()
    {
        // Fetch the total number of visitors
        $visitorCount = Visitor::distinct('ip_address')->count('ip_address');
        $seller = Seller::count();

        $conversionRate = $visitorCount > 0 ? ($seller / $visitorCount) * 100 : 0;

        // Fetch visitors for the past 7 days
        $visitors = Visitor::where('created_at', '>=', now()->subDays(7))->get();

        // Fetch top countries
        $countries = Visitor::select('country', DB::raw('count(distinct ip_address)  as total'))
                            ->groupBy('country')
                            ->orderBy('total', 'desc')
                            ->take(5)
                            ->get();
                

        // Fetch top devices
        $devices = Visitor::select('device', DB::raw('count(distinct ip_address)  as total'))
                          ->groupBy('device')
                          ->orderBy('total', 'desc')
                          ->take(5)
                          ->get();

        // Fetch top browsers
        $browsers = Visitor::select('browser', DB::raw('count(distinct ip_address)  as total'))
                           ->groupBy('browser')
                           ->orderBy('total', 'desc')
                           ->take(5)
                           ->get();

        // Fetch top platforms
        $platforms = Visitor::select('platform', DB::raw('count(distinct ip_address)  as total'))
                            ->groupBy('platform')
                            ->orderBy('total', 'desc')
                            ->take(5)
                            ->get();


        return response()->json([
            'status' => true,
            'visitorCount' => $visitorCount,
            'visitors' => $visitors,
            'countries' => $countries,
            'devices' => $devices,
            'browsers' => $browsers,
            'platforms' => $platforms,
            'seller' => $seller,
            'conversionRate' => $conversionRate
        ]);

        // return view('center.analytics', compact('visitorCount', 'visitors', 'countries', 'devices', 'browsers', 'platforms', 'seller', 'conversionRate'));
    }
}
