<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GroupProduct;
use App\Models\PaymentGateways;
use App\Models\Product;
use App\Models\ProductPaymentGateway;
use App\Models\ProductVariantSerial;
use App\Models\ProductVariantValue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function index()
    {
        $data = Product::where('is_group', 1)
                            ->when(request('search'), function ($query) {
                                $query->where('title', 'like', '%' . request('search') . '%');
                            })->when(request('start_date') && request('end_date'), function ($query) {
                                $query->whereBetween('created_at', [Carbon::parse(request('start_date'))->startOfDay(), Carbon::parse(request('end_date'))->endOfDay()]);
                            })
                            ->select('id', 'title', 'image', 'description', 'position', 'created_at')
                            ->orderBy('position', 'asc')
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



    public function getProducts()
    {
        $products = Product::where('is_group', 0)
                            ->where('user_id', User::where('email', Auth::user()->email)->first()->id)
                            ->select('id', 'title')
                            ->orderBy('title', 'asc')
                            ->get();

        return response()->json([
            'status' => true,
            'data'   => $products,
        ], 200);
    }



    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            "title"              => "required|max:255",
            "description"        => "required|max:10000",
            'image'              => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'product_id'         => 'required|array|min:1',
            'product_id.*'       => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $image_path = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagename = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/group/images'), $imagename);
                $image_path = '/uploads/group/images/' . $imagename;
            }
    
            $product = new Product();
            $product->title        =  $request->title;
            $product->slug         =  Str::slug($request->title).'-'.time();
            $product->description  =  $request->description;
            $product->user_id      =  User::where('email', Auth::user()->email)->first()->id;
            $product->image        =  $image_path;
            $product->is_group     =  1;
            $product->save();

            foreach ($request->product_id as $product_id) {

                GroupProduct::create([
                   'product_id' => $product_id,
                   'group_id'   => $product->id
                ]);

                // $pre_product = Product::findOrFail($product_id);
                // $pre_product->show_status = 0;
                // $pre_product->save();
            }


            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Group created successfully',
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }

     
    }


    public function view($id)
    {
        $group    = Product::with(['groupProducts'])->find($id);
        
        if (!$group || $group->is_group != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Group not found',
            ], 404);
        }

        
        return response()->json([
            'status' => true,
            'data'   => $group,
        ], 200);

    }


    public function update(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            "title"              => "required|max:255",
            "description"        => "required|max:10000",
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'product_id'         => 'required|array|min:1',
            'product_id.*'       => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }

        DB::beginTransaction();

        try {
            
            $image_path = null;
    
            if ($request->hasFile('image')) {
    
                $image = $request->file('image');
                $imagename = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products/images'), $imagename);
                $image_path = '/uploads/products/images/' . $imagename;
            }
    
            $product = Product::find($request->id);
            $product->title        =  $request->title;
            $product->slug         =  Str::slug($request->title).'-'.time();
            $product->description  =  $request->description;
            $product->user_id      =  User::where('email', Auth::user()->email)->first()->id;
            $product->image        =  $image_path ?? $product->image;
            $product->is_group     =  1;
            $product->save();


            GroupProduct::where('group_id', $product->id)->delete();
            
            foreach ($request->product_id as $product_id) {
                GroupProduct::create([
                   'product_id' => $product_id,
                   'group_id'   => $product->id
                ]);

                // $pre_product = Product::findOrFail($product_id);
                // $pre_product->show_status = 0;
                // $pre_product->save();
            }


            DB::commit();
    
            return response()->json([
                'status'  => true,
                'message' => 'Group updated successfully',
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }


    public function destroy($id){

        $product = Product::find($id);

        if (!$product || $product->is_group != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found'
            ], 404);
        }
        $product->delete();

        $group_products = GroupProduct::where('group_id', $id)->get();
        foreach ($group_products as $group_product) {
            $group_product->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully'
        ], 200);
        
    }


    // public function status($id){

    //     $product = Product::findOrFail($id);
    //     $product->show_status = !$product->show_status;
    //     $product->save();

    //     return redirect()->route('products.index')->with('success','Product deleted successfully');
        
    // }
}
