<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\EmailJob;
use App\Mail\AdminCustomEmail;
use App\Mail\CustomEmail;
use App\Models\Seller;
use App\Models\SellerContact;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

class SellerController extends Controller
{
    public function index()
    {
        $sellers = Seller::when(request("search"), function ($query) {
                                $query->where("name","like","%". request("search")  ."%")
                                    ->orWhere("email","like","%". request("search")  ."%")
                                    ->orWhere("subdomain","like","%". request("search")  ."%")
                                    ->orWhere("country","like","%". request("search")  ."%");
                            })  
                            -> latest("id")
                            ->paginate(request('per_page', 20));


        return response()->json([
            'status' => true,
            'data'   => $sellers->items(),
            'page_count' => [
                'current_page' => $sellers->currentPage(),
                'last_page'    => $sellers->lastPage(),
                'per_page'     => $sellers->perPage(),
                'total'        => $sellers->total(),
            ],
            'links' => [
                'first' => $sellers->url(1),
                'last'  => $sellers->url($sellers->lastPage()),
                'prev'  => $sellers->previousPageUrl(),
                'next'  => $sellers->nextPageUrl(),
            ]
        ], 200);
    }


    public function bulkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject'       => 'required|string',
            'message'       => 'required|string',
            'seller_type'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }


        $orders =   Seller::when(request()->has("seller_type"), function ($query) {
                                if (request()->seller_type != null) {
                                    if (request()->seller_type != "all") {
                                        $query->where("status", request()->seller_type);
                                    }
                                }
                            })
                            ->get();

        foreach ($orders as $order) {
            // Mail::to($order->email)->queue(new AdminCustomEmail(
            //     $request->subject,
            //     $request->message,
            // ));

            $body = view('emails.admin_custom_email', ['subject' => $request->subject, 'data' => $request->message])->render();
            dispatch(new EmailJob($order->email, $request->subject, $order->name, $body));

            // noReplayMail($order->email, $request->subject, $order->name, $body);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email sent successfully'
        ],200);
    }



    public function sellerLog($id)
    {
        $seller = Seller::find($id);

        if (!$seller) {
            return response()->json([
                'status' => false,
                'message' => 'Seller not found'
            ], 404);
        }

        $logs   = DB::table('tenant'.$seller->subdomain.'.activity_log')
                    ->select('tenant'.$seller->subdomain.'.activity_log.id', 'tenant'.$seller->subdomain.'.activity_log.log_name', 'tenant'.$seller->subdomain.'.activity_log.event', 'tenant'.$seller->subdomain.'.activity_log.description','tenant'.$seller->subdomain.'.activity_log.created_at')
                    ->latest('id')
                    ->paginate(request('per_page', 20));


        return response()->json([
            'status' => true,
            'data'   => $logs->items(),
            'page_count' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
            ],
            'links' => [
                'first' => $logs->url(1),
                'last'  => $logs->url($logs->lastPage()),
                'prev'  => $logs->previousPageUrl(),
                'next'  => $logs->nextPageUrl(),
            ]
        ], 200);
    }


    public function sellerOrder($id)
    {
        $seller = Seller::find($id);

        if (!$seller) {
            return response()->json([
                'status' => false,
                'message' => 'Seller not found'
            ], 404);
        }

        $orders = DB::table('tenant'.$seller->subdomain.'.orders')
                        // ->where('payment_status', 1)
                        ->join('tenant'.$seller->subdomain.'.products', 'tenant'.$seller->subdomain.'.orders.product_id', '=', 'tenant'.$seller->subdomain.'.products.id')
                        ->select('tenant'.$seller->subdomain.'.orders.uuid', 'tenant'.$seller->subdomain.'.products.title as product_title', 'tenant'.$seller->subdomain.'.orders.name', 'tenant'.$seller->subdomain.'.orders.email', 'tenant'.$seller->subdomain.'.orders.payment_status', 'tenant'.$seller->subdomain.'.orders.order_note', 'tenant'.$seller->subdomain.'.orders.sub_total', 'tenant'.$seller->subdomain.'.orders.discount', 'tenant'.$seller->subdomain.'.orders.total', 'tenant'.$seller->subdomain.'.orders.payment_method', 'tenant'.$seller->subdomain.'.orders.created_at')
                        ->latest()
                        ->paginate(request('per_page', 20));



        
        return response()->json([
            'status' => true,
            'data'   => $orders->items(),
            'page_count' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
            'links' => [
                'first' => $orders->url(1),
                'last'  => $orders->url($orders->lastPage()),
                'prev'  => $orders->previousPageUrl(),
                'next'  => $orders->nextPageUrl(),
            ]
        ], 200);
    }


    public function status(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status'       => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }



        $seller = Seller::find($id);

        if (!$seller) {
            return response()->json([
                'status' => false,
                'message' => 'Seller not found'
            ], 404);
        }

        $seller->status = $request->status;
        $seller->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ], 200);
    }


    public function delete($id)
    {
        $seller = Seller::find($id);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found'
            ], 404);
        }

        try {
            $tenant = Tenant::find($seller->subdomain);

            if ($tenant == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found'
                ], 404);
            }

            $tenant->delete();
            $seller->delete();
            DB::table('users')->where('email', $seller->email)->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Seller deleted successfully'
            ], 200);
    
        } catch (TenantCouldNotBeIdentifiedById $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

    }


    // public function contact()
    // {
    //     $contacts = SellerContact::latest()->paginate(15);

    //     return view("center.seller.contact",compact("contacts"));
    // }

    // public function contactDelete($id)
    // {
    //     $contacts = SellerContact::findOrFail($id);
    //     $contacts->delete();

    //     return redirect()->back()->with("message","Contact deleted successfully");
    // }

    // public function sellerLogin($id)
    // {
    //     $seller = Seller::findOrFail($id);

    //     $user = User::where("email",$seller->email)->first();

    //     return redirect()->route('aAdmin.login',[
    //         'email'    => $user->email,
    //         'password' => $user->password,
    //     ]);
    // }

    // public function paymentSetting(Request $request,$id)
    // {
    //     $seller = Seller::findOrFail($id);
    //     $seller->sellhub_payment_type = $request->seller_payment_gateway;
    //     $seller->save();


    //     $seller_setting = DB::table('tenant'.$seller->subdomain.'.general_settings')->first();

    //     // Option 1: Update using the query builder
    //     DB::table('tenant'.$seller->subdomain.'.general_settings')
    //         ->where('id', $seller_setting->id) // or whatever your primary key is
    //         ->update([
    //             'sellhub_payment_type' => $seller->sellhub_payment_type
    //         ]);

    //     return redirect()->back()->with("message","Payment gateway updated successfully");

    // }

    // public function paymentGatewayChange(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'seller_payment_gateway' => 'required|in:1,2,3',
    //         'seller_email'           => 'required|email',
    //         'token'                  => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors'  => $validator->errors()
    //         ], 422);
    //     }


    //     if ($request->token != "sdfuyaf54fg+g4fasd4ty7sad8fgf") {
    //         return response()->json([
    //             'success' => false,
    //             'errors'  => "Invalid token"
    //         ], 422);
    //     }


    //     $seller = Seller::where("email",$request->seller_email)->first();

    //     if ($seller == null) {
    //         return response()->json([
    //             'success' => false,
    //             'errors'  => "Invalid email"
    //         ], 422);
    //     }
    //     $seller->sellhub_payment_type = $request->seller_payment_gateway;
    //     $seller->save();

    //     $seller_setting = DB::table('tenant'.$seller->subdomain.'.general_settings')->first();

    //     // Option 1: Update using the query builder
    //     DB::table('tenant'.$seller->subdomain.'.general_settings')
    //         ->where('id', $seller_setting->id) // or whatever your primary key is
    //         ->update([
    //             'sellhub_payment_type' => $seller->sellhub_payment_type
    //         ]);


    //     return response()->json([
    //         'success' => true,
    //         'message' => "Payment gateway updated successfully"
    //     ], 200);
    // }

}
