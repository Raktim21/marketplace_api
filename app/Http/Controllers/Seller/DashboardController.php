<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {

                if (request()->has('filter')) {

            if (request('filter') == 'today') {

                $total_sell         = Order::where("payment_status", 1)->whereBetween('created_at', [Carbon::now()->subHours(24), Carbon::now()])->count();
                $total_order_amount = Order::where("payment_status", 1)->whereBetween('created_at', [Carbon::now()->subHours(24), Carbon::now()])->sum('total');
                $total_product      = Product::whereBetween('created_at', [Carbon::now()->subHours(24), Carbon::now()])->count();

                if (Order::whereBetween('created_at', [Carbon::now()->subHours(24), Carbon::now()])->count() != 0) {
                    $conversion_rate    = ($total_sell/Order::whereBetween('created_at', [Carbon::now()->subHours(24), Carbon::now()])->count())*100;
                }else {
                    $conversion_rate    = 0;
                }

                $topProducts = Product::withCount(['orders as times_sold' => function($query) {
                                    $query->where('payment_status', 1)
                                          ->whereBetween('created_at', [Carbon::now()->subHours(24), Carbon::now()]);
                                }])
                                ->withSum(['orders as total_sales' => function($query) {
                                    $query->where('payment_status', 1)
                                          ->whereBetween('created_at', [Carbon::now()->subHours(24), Carbon::now()]);
                                }], 'total')
                                ->orderByDesc('total_sales')
                                ->take(10)
                                ->get();

                $months = [];
                $monthly_order = []; 
        
                for($i = 24; $i >= 1; $i -= 1){
                    $months[]        = $i . ' hours ago';
                    $monthly_order[] = Order::whereBetween('created_at', [Carbon::now()->subHours($i),Carbon::now()->subHours($i - 1)])
                                                        ->where("payment_status", 1)
                                                        ->sum('total');
                }


                $stats = DB::table('orders')
                        ->select(
                            'payment_method',
                            DB::raw('COUNT(*) as gateway_count'),
                        )
                        ->where('payment_status', 1)
                        ->whereBetween('created_at', [Carbon::now()->subHours(24), Carbon::now()])
                        ->groupBy('payment_method')
                        ->orderBy('gateway_count', 'desc')
                        ->get();


            }


            if (request('filter') == 'week') {

                $total_sell         = Order::where("payment_status", 1)->whereBetween('created_at', [Carbon::now()->subHours(168), Carbon::now()->addHour()])->count();
                $total_order_amount = Order::where("payment_status", 1)->whereBetween('created_at', [Carbon::now()->subHours(168), Carbon::now()->addHour()])->sum('total');
                $total_product      = Product::whereBetween('created_at', [Carbon::now()->subHours(168), Carbon::now()])->count();

                if (Order::whereBetween('created_at', [Carbon::now()->subHours(168), Carbon::now()])->count() != 0) {
                    $conversion_rate    = ($total_sell/Order::whereBetween('created_at', [Carbon::now()->subHours(168), Carbon::now()->addHour()])->count())*100;
                }else {
                    $conversion_rate    = 0;
                }

                $topProducts = Product::withCount(['orders as times_sold' => function($query) {
                                $query->where('payment_status', 1)
                                      ->whereBetween('created_at', [Carbon::now()->subHours(168), Carbon::now()->addHour()]);
                            }])
                            ->withSum(['orders as total_sales' => function($query) {
                                $query->where('payment_status', 1)
                                      ->whereBetween('created_at', [Carbon::now()->subHours(168), Carbon::now()->addHour()]);
                            }], 'total')
                            ->orderByDesc('total_sales')
                            ->take(10)
                            ->get();
        
                $months        = [];
                $monthly_order = []; 
                $weekStart     = Carbon::now()->subHours(168); // Monday
                $weekEnd       = Carbon::now()->addHour();     // Sunday

                for ($i = 168; $i >= 1; $i -= 24) {
                    $months[]        = Carbon::now()->subHours($i)->format('d M, Y');
                    $monthly_order[] = Order::whereBetween('created_at', [Carbon::now()->subHours($i), Carbon::now()->subHours($i - 24)])
                                                        ->where("payment_status", 1)
                                                        ->sum('total');
                }

                $stats = DB::table('orders')
                        ->select(
                            'payment_method',
                            DB::raw('COUNT(*) as gateway_count'),
                           
                        )
                        ->where('payment_status', 1)
                        ->whereBetween('created_at', [Carbon::now()->subHours(168), Carbon::now()])
                        ->groupBy('payment_method')
                        ->orderBy('gateway_count', 'desc')
                        ->get();


            }


            if (request('filter') == 'month') {

                $total_sell         = Order::where("payment_status", 1)->whereBetween('created_at', [Carbon::now()->subHours(720), Carbon::now()])->count();
                $total_order_amount = Order::where("payment_status", 1)->whereBetween('created_at', [Carbon::now()->subHours(720), Carbon::now()])->sum('total');
                $total_product      = Product::whereBetween('created_at', [Carbon::now()->subHours(720), Carbon::now()])->count();

                if (Order::whereBetween('created_at', [Carbon::now()->subHours(720), Carbon::now()])->count() != 0) {
                    $conversion_rate    = ($total_sell/Order::whereBetween('created_at', [Carbon::now()->subHours(720), Carbon::now()])->count())*100;
                }else {
                    $conversion_rate    = 0;
                }

                $topProducts = Product::withCount(['orders as times_sold' => function($query) {
                                $query->where('payment_status', 1)
                                      ->whereBetween('created_at', [Carbon::now()->subHours(720), Carbon::now()]);
                            }])
                            ->withSum(['orders as total_sales' => function($query) {
                                $query->where('payment_status', 1)
                                      ->whereBetween('created_at', [Carbon::now()->subHours(720), Carbon::now()]);
                            }], 'total')
                            ->orderByDesc('total_sales')
                            ->take(10)
                            ->get();
        
                $months        = [];
                $monthly_order = [];

                for ($i = 720; $i >= 1; $i -= 24) {
                    $months[]        = Carbon::now()->subHours($i)->format('d M, Y');
                    $monthly_order[] = Order::whereBetween('created_at', [Carbon::now()->subHours($i), Carbon::now()->subHours($i - 24)])
                                                        ->where("payment_status", 1)
                                                        ->sum('total');
                }


                $stats = DB::table('orders')
                        ->select(
                            'payment_method',
                            DB::raw('COUNT(*) as gateway_count'),
                           
                        )
                        ->where('payment_status', 1)
                        ->whereBetween('created_at', [Carbon::now()->subHours(720), Carbon::now()])
                        ->groupBy('payment_method')
                        ->orderBy('gateway_count', 'desc')
                        ->get();

            }


            if (request('filter') == 'custom') {

                $startDate    = Carbon::parse(request('start_date'));
                $endDate      = Carbon::parse(request('end_date'));


                $total_sell         = Order::where("payment_status", 1)->whereBetween('created_at', [$startDate, $endDate])->count();
                $total_order_amount = Order::where("payment_status", 1)->whereBetween('created_at', [$startDate, $endDate])->sum('total');
                $total_product      = Product::whereBetween('created_at', [$startDate, $endDate])->count();
        

                if (Order::whereBetween('created_at', [$startDate, $endDate])->count() != 0) {
                    $conversion_rate    = ($total_sell/Order::whereBetween('created_at', [$startDate, $endDate])->count())*100;
                }else {
                    $conversion_rate    = 0;
                }

                $topProducts = Product::withCount(['orders as times_sold' => function($query) use ($startDate, $endDate) {
                                            $query->where('payment_status', 1)
                                                  ->whereBetween('created_at', [$startDate, $endDate]);
                                        }])
                                        ->withSum(['orders as total_sales' => function($query) use ($startDate, $endDate) {
                                            $query->where('payment_status', 1)
                                                  ->whereBetween('created_at', [$startDate, $endDate]);
                                        }], 'total')
                                        ->orderByDesc('total_sales')
                                        ->take(10)
                                        ->get();

                $daysDifference = $startDate->diffInDays($endDate);

                $total_sell = Order::where("payment_status", 1)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $total_order_amount = Order::where("payment_status", 1)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('total');

                $total_product = Product::whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $months = [];
                $monthly_order = [];

                if ($daysDifference <= 30) {
                    // Daily data
                    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                        $startOfDay = $date->copy()->startOfDay();
                        $endOfDay = $date->copy()->endOfDay();
                        
                        $months[] = $date->format('d M, Y');
                        $monthly_order[] = Order::whereBetween('created_at', [$startOfDay, $endOfDay])
                            ->where("payment_status", 1)
                            ->sum('total');
                    }
                } elseif ($daysDifference <= 120) {
                    // Weekly data
                    $currentWeek = $startDate->copy()->startOfWeek();
                    while ($currentWeek->lte($endDate)) {
                        $weekEnd = $currentWeek->copy()->endOfWeek()->min($endDate);
                        
                        $months[] = 'Week ' . $currentWeek->weekOfYear . ', ' . $currentWeek->year;
                        $monthly_order[] = Order::whereBetween('created_at', [
                                $currentWeek->startOfWeek(), 
                                $weekEnd
                            ])
                            ->where("payment_status", 1)
                            ->sum('total');
                        
                        $currentWeek->addWeek()->startOfWeek();
                    }
                } else {
                    // Monthly data
                    $currentMonth = $startDate->copy()->startOfMonth();
                    while ($currentMonth->lte($endDate)) {
                        $monthEnd = $currentMonth->copy()->endOfMonth()->min($endDate);
                        
                        $months[] = $currentMonth->format('M, Y');
                        $monthly_order[] = Order::whereBetween('created_at', [
                                $currentMonth->startOfMonth(), 
                                $monthEnd
                            ])
                            ->where("payment_status", 1)
                            ->sum('total');
                        
                        $currentMonth->addMonth()->startOfMonth();
                    }
                }


                $stats = DB::table('orders')
                        ->select(
                            'payment_method',
                            DB::raw('COUNT(*) as gateway_count'),
                        )
                        ->where('payment_status', 1)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->groupBy('payment_method')
                        ->orderBy('gateway_count', 'desc')
                        ->get();

            }

        }else {
            $total_sell         = Order::where("payment_status", 1)->count();
            $total_order_amount = Order::where("payment_status", 1)->sum('total');
            $total_product      = Product::count();

            $topProducts = Product::withCount(['orders as times_sold' => function($query) {
                                $query->where('payment_status', 1);
                            }])
                            ->withSum(['orders as total_sales' => function($query) {
                                $query->where('payment_status', 1);
                            }], 'total')
                            ->orderByDesc('total_sales')
                            ->take(10)
                            ->get();

            if (Order::count() != 0) {
                $conversion_rate    = ($total_sell/Order::count())*100;
            }else {
                $conversion_rate    = 0;
            }
    
            $months = [];
            $monthly_order = []; 
            // dd($total_order_amount);
            for($i = 365; $i >= 0; $i -= 30){

                $date = Carbon::now()->subDays($i);

                $months[] = $date->format('M, Y');
            
                $monthly_order[] = Order::whereYear('created_at', $date->year)
                                        ->whereMonth('created_at', $date->month)
                                        ->where('payment_status', 1)
                                        ->sum('total');

            }


            $stats = DB::table('orders')
                    ->select(
                        'payment_method',
                        DB::raw('COUNT(*) as gateway_count'),
                    )
                    ->where('payment_status', 1)
                    ->groupBy('payment_method')
                    ->orderBy('gateway_count', 'desc')
                    ->get();

        }

        $payment_gateways = [];
        $payment_gateways_count = [];

        foreach ($stats as $stat) {
            $payment_method = $stat->payment_method;

            if ($payment_method == 'Sellhub') {
                $payment_method = 'Credit/Debit Card';
            }
            if ($payment_method == 'Stripe') {
                $payment_method = 'Credit/Debit Card (Stripe)';
            }
    
            $payment_gateways[] = $payment_method;
            $payment_gateways_count[] = $stat->gateway_count;
        }

        // return view('tenant.dashboard.dashboard', compact('total_sell','total_order_amount', 'total_product','months','monthly_order','conversion_rate','topProducts','payment_gateways','payment_gateways_count'));


 
        return response()->json([
            'status' => true,
            'data' => [
                'total_sell' => $total_sell,
                'total_order_amount' => $total_order_amount,
                'total_product' => $total_product,
                'chart_data' => [
                    'data'   => $months,
                    'date'   => $monthly_order,
                ],
                'conversion_rate' => $conversion_rate,
                'top_products' => $topProducts,
                'payment_gateways' => $payment_gateways,
                'payment_gateways_count' => $payment_gateways_count,
            ]
        ]);
    }


}
