<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index()
    {
        $data = PromoCode::when(request('search'), function ($query) {
                                $query->where('code', 'like', '%' . request('search') . '%');
                            })
                            ->latest()
                            ->paginate(request('per_page', 20));

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



    public function view($id)
    {
        $promo = PromoCode::find($id);

        if (!$promo) {
            return response()->json([
                'status' => false,
                'message'   => 'Coupon not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $promo
        ], 200);
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'             => 'required',
            'max_number_uses'  => 'required|numeric|min:1',
            'product_id'       => ['required','numeric', function ($attribute, $value, $fail) {
                                        if ($value != '0') {
                                            $product = Product::find($value);
                                            if (!$product) {
                                                $fail('Please select a valid product.');
                                            }
                                        }
                                    }],
            'is_percent'       => 'required|boolean',
            'amount'           => ['required','numeric','min:1', function ($attribute, $value, $fail) {
                                        if (request()->input('is_percent') == 1) {
                                            if ($value > 100) {
                                                $fail('Please enter a valid percentage.');
                                            }
                                        }
                                    }],
            'expires_at'       => 'required|date|after_or_equal:today',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }



        $promo = new Promocode();
        $promo->code             = $request->code;
        $promo->max_number_uses  = $request->max_number_uses;
        if ($request->product_id == 0) {
            $promo->is_global_product = true;
        }else {
            $promo->is_global_product = false;
            $promo->product_id       = $request->product_id;
        }
        $promo->is_percent       = $request->is_percent;
        $promo->amount           = $request->amount;
        $promo->expires_at       = $request->expires_at;
        $promo->save();


        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully'
        ], 201);
      
    }



    public function update(Request $request, $id)
    {
       $validator = Validator::make($request->all(), [
            'code'             => 'required',
            'max_number_uses'  => 'required|numeric|min:1',
            'product_id'       => ['required','numeric', function ($attribute, $value, $fail) {
                                        if ($value != '0') {
                                            $product = Product::find($value);
                                            if (!$product) {
                                                $fail('Please select a valid product.');
                                            }
                                        }
                                    }],
            'is_percent'       => 'required|boolean',
            'amount'           => ['required','numeric','min:1', function ($attribute, $value, $fail) {
                                        if (request()->input('is_percent') == 1) {
                                            if ($value > 100) {
                                                $fail('Please enter a valid percentage.');
                                            }
                                        }
                                    }],
            'expires_at'       => 'required|date|after_or_equal:today',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }



        $promo = Promocode::find($id);
        if (!$promo) {
            return response()->json([
                'status' => false,
                'message'   => 'Coupon not found'
            ], 404);
        }
        $promo->code             = $request->code; 
        $promo->max_number_uses  = $request->max_number_uses;
        if ($request->product_id == 0) {
            $promo->is_global_product = true;
            $promo->product_id        = null;
        }else {
            $promo->is_global_product = false;
            $promo->product_id        = $request->product_id;
        }
        $promo->is_percent       = $request->is_percent;
        $promo->amount           = $request->amount;
        $promo->expires_at       = $request->expires_at;
        $promo->save();


        return response()->json([
            'success' => true,
            'message' => 'Coupon updated successfully'
        ], 200);
      
    }


    public function destroy($id)
    {
        $product = PromoCode::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message'   => 'Coupon not found'
            ], 404);
        }

        $product->delete();


        return response()->json([
            'status' => true,
            'message'   => 'Coupon deleted successfully'
        ], 200);
    }
}
