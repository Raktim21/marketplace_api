<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Mail\CustomEmail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Jobs\EmailJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $data = Order::when(request()->search, function ($query) {
                            $search = urldecode(request()->search);
                            $query->where(function($subQuery) use ($search) {
                                $subQuery->where('uuid', $search)
                                        ->orWhere('email', 'like', '%' . $search . '%')
                                        ->orWhere('name', 'like', '%' . $search . '%');
                            });
                        })
                        ->when(request()->payment_status != null, function ($query) {
                            if (request()->payment_status != null) {
                                $query->where("payment_status", request()->payment_status);
                            }
                        })
                        ->when(request()->from != null && request()->to != null, function ($query) {
                            $query->whereBetween('created_at', [
                                Carbon::parse(request()->from)->startOfDay(),
                                Carbon::parse(request()->to)->endOfDay()
                            ]);
                        })
                        ->when(request()->payment_method != null , function ($query) {
                            $query->where("payment_method", request()->payment_method);
                        })
                        ->when(request()->has("filter_type") && request()->filter_type != null, function ($query) {
                        
                            if (request()->filter_type == "last_24_hours") {
                                $query->whereBetween('created_at', [Carbon::now()->subHours(24), Carbon::now()]);
                            }elseif (request()->filter_type == "last_30_hours") {
                                $query->whereBetween('created_at', [Carbon::now()->subHours(30), Carbon::now()]);
                            }elseif (request()->filter_type == "last_48_hours") {
                                $query->whereBetween('created_at', [Carbon::now()->subHours(48), Carbon::now()]);
                            }elseif(request()->filter_type == "last_7_days") {
                                $query->whereBetween('created_at', [Carbon::now()->subHours(168), Carbon::now()]); 
                            }elseif(request()->filter_type == "last_30_days") {
                                $query->whereBetween('created_at', [Carbon::now()->subHours(720), Carbon::now()]); 
                            }
                        })
                        ->with([
                            'product' => function ($query) {
                                $query->select('id', 'title', 'image');
                            },
                            "customFields"
                        ])
                        ->latest('id')
                        ->paginate(request()->per_page ?? 20);


        foreach ($data as $order) {
            if ($order->payment_status == 0) {
                if (Carbon::parse($order->created_at)->addDay() < Carbon::now()) {
                    $order->payment_status = 2;
                    $order->save();
                }
            }
        }

        return response()->json([
            'status' => true,
            'data'   => $data->items(),
            'page_count' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
            'links' => [
                'first' => $data->url(1),
                'last'  => $data->url($data->lastPage()),
                'prev'  => $data->previousPageUrl(),
                'next'  => $data->nextPageUrl(),
            ]
        ], 200);
    }



    public function analytics()
    {
        $total_revenue          = Order::where('payment_status', 1)->sum('total');
        $total_complete_invoice = Order::where('payment_status', 1)->count();
        $total_pending_invoice  = Order::where('payment_status', 0)->count();
        $total_expired_invoice  = Order::where('payment_status', 2)->count();

        return response()->json([
            'status' => true,
            'total_revenue' => $total_revenue,
            'total_complete_invoice' => $total_complete_invoice,
            'total_pending_invoice' => $total_pending_invoice,
            'total_expired_invoice' => $total_expired_invoice,
        ], 200);
    }



    public function view($id)
    {
        $order = Order::where('id', $id)->with('product', 'variant', 'review', 'customFields')->first();
        return response()->json([
            'status' => true,
            'data'   => $order,
        ], 200);
    }



    public function bulkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject'       => 'required|string',
            'message'       => 'required|string',
            'invoice_type'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }



        $orders = Order::when(request()->has("invoice_type"), function ($query) {
                        if (request()->invoice_type != null) {
                            if (request()->invoice_type != "all") {
                                $query->where("payment_status", request()->invoice_type);
                            }
                        }
                    })
                    ->select('email')
                    ->distinct()
                    ->latest()
                    ->get();


        foreach ($orders as $order) {
            $body = view('emails.custom_email', ['subject' => $request->subject, 'data' => $request->message])->render();
            dispatch(new EmailJob($order->email, $request->subject, $order->name, $body));
        }

        return redirect()->back()->with('success', 'Email sent successfully');
    }



    public function downloadInvoice($id)
    {
        $invoice = Order::where('id', $id)->with('product')->first();
        $pdf     = Pdf::loadView('tenant.dashboard.pdf.seller_invoice', compact('invoice'));
        return $pdf->download('invoice-' . $invoice->id . '.pdf');
    }
}
