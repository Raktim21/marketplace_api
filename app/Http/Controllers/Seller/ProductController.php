<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Mail\ProductRestockEmail;
use App\Mail\ProductStockOutMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentGateways;
use App\Models\Product;
use App\Models\ProductPaymentGateway;
use App\Models\ProductVariants;
use App\Models\ProductVariantSerial;
use App\Models\ProductVariantValue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Jobs\EmailJob;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $data = Product::where('is_group', 0)->when(request('search'), function ($query) {
                                $query->where('title', 'like', '%' . request('search') . '%');
                            })->when(request('start_date') && request('end_date'), function ($query) {
                                $query->whereBetween('created_at', [Carbon::parse(request('start_date'))->startOfDay(), Carbon::parse(request('end_date'))->endOfDay()]);
                            })
                            ->with(['variants.serials'])
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



    public function view($id)
    {
        $product = Product::with('variants.serials')->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product_order_count  = Order::where('product_id', $id)->where('payment_status', 1)->count();
        $product_order_amount = Order::where('product_id', $id)->where('payment_status', 1)->sum('total');

        return response()->json([
            'status' => true,
            'data'   => $product,
            'product_order_count' => $product_order_count,
            'product_order_amount' => $product_order_amount
        ], 200);
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title"              => "required|max:255",
            "description"        => "required|max:4294967195",
            'image'              => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'email_message'      => 'required|max:4294967195',
            'veriant'            => 'required|array|min:1',
            'meta_title'         => 'nullable|string|max:255',
            'meta_description'   => 'nullable|string|max:255',
            'email_button_text'  => 'nullable|string',
            'email_button_url'   => 'nullable|url',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }

        if($request->email_button_text != null && $request->email_button_url == null)
        {
            return response()->json([
                'success' => false,
                'message' => 'Please enter the button URL'
            ], 422);
        }

        if($request->email_button_text == null && $request->email_button_url != null)
        {
            return response()->json([
                'success' => false,
                'message' => 'Please enter the button text'
            ], 422);
        }

        if ($request->email_message == "<p><br></p>") {
            return response()->json([
                'success' => false,
                'message' => 'Email message is required'
            ], 422);       
        }
    

        DB::beginTransaction();

        try {
            
            $file_path = null;
            $image_path = null;

    
            if ($request->hasFile('image')) {
    
                $image = $request->file('image');
                $imagename = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products/images'), $imagename);
                $image_path = '/uploads/products/images/' . $imagename;
            }
    
            $product = new Product();
            $product->title             = $request->title;
            $product->slug              = Str::slug($request->title).'-'.time();
            $product->description       = $request->description;
            $product->email_message     = $request->email_message;
            $product->user_id           = User::where('email', Auth::user()->email)->first()->id;
            $product->image             = $image_path;
            $product->meta_title        = $request->meta_title;
            $product->meta_description  = $request->meta_description;
            $product->email_button_text = $request->email_button_text ?? null;
            $product->email_button_url  = $request->email_button_url ?? null;
            $product->position          = 1;
            $product->save();


            foreach ($request->veriant as $key => $veriant) {

                if (!isset($veriant['variant_name']) || $veriant['variant_name'] == null) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Variant name is required'
                    ], 422);
                }

                if (!isset($veriant['variant_price']) || $veriant['variant_price'] == null) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Variant price is required'
                    ], 422);
                }

                if (!isset($veriant['product_type']) || $veriant['product_type'] == null) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Product type is required'
                    ], 422);
                }

                if (!isset($veriant['is_default']) || $veriant['is_default'] == null) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Is default is required'
                    ], 422);
                }


                $product_variants = $product->variants()->create([
                                        'name' => $veriant['variant_name'],
                                        'price' => $veriant['variant_price'],
                                        'product_type' => $veriant['product_type'],
                                        'is_default' => $veriant['is_default']
                                    ]);


                if(!isset($veriant['product_type']) || $veriant['product_type'] == null || ($veriant['product_type'] != 'Serial' && $veriant['product_type'] != 'File' && $veriant['product_type'] != 'Text' && $veriant['product_type'] != 'Webhook')) {

                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid product type'
                    ], 422);
                }



                if ($veriant['product_type'] == 'Serial') {

                    if (!isset($veriant['serial']) || $veriant['serial'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Serial is required'
                        ], 422);
                    }


                    if (!isset($veriant['serial_delimiter']) || $veriant['serial_delimiter'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Serial delimiter is required'
                        ], 422);
                    }


                    if (!isset($veriant['min_quantity']) || $veriant['min_quantity'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Minimum quantity is required'
                        ], 422);
                    }


                    if (!isset($veriant['max_quantity']) || $veriant['max_quantity'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Maximum quantity is required'
                        ], 422);
                    }

                    $serials  = explode($veriant['serial_delimiter'] == '/n' ? PHP_EOL : $veriant['serial_delimiter'], $veriant['serial']);
                    $product_variants['serial_delimiter']  = $veriant['serial_delimiter'];

                    foreach ($serials as $serial) {
                        ProductVariantSerial::create([
                            'product_variant_id' => $product_variants->id,
                            'serial' => $serial
                        ]);
                    }

                    $product_variants['min_quantity']      = $veriant['min_quantity'];
                    $product_variants['max_quantity']      = $veriant['max_quantity'];
                    $product_variants->save();

                }elseif ($veriant['product_type'] == 'File') {

                    if (!isset($veriant['file']) || $veriant['file'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'File is required'
                        ], 422);
                    }

                    $file = $veriant['file'];
                    $filename = time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/files'), $filename);
                    $file_path = '/uploads/files/' . $filename;
                    $product_variants->file  = $file_path;
                    $product_variants->save();
         

                }elseif ($veriant['product_type'] == 'Text') {

                    if (!isset($veriant['text']) || $veriant['text'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Text is required'
                        ], 422);
                    }

                    $product_variants->text  = $veriant['text'];
                    $product_variants->save();

         
                }elseif ($veriant['product_type'] == 'Webhook') {

                    if (!isset($veriant['webhook']) || $veriant['webhook'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Webhook URL is required'
                        ], 422);
                    }

                    $product_variants->text  = $veriant['webhook'];
                    $product_variants->save();
                }

            }

            $empty_serial = ProductVariantSerial::where('serial', '')->get();
            foreach ($empty_serial as $serial) {
                $serial->delete();
            }


            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            // dd($th->getMessage());
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }

     
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "title"              => "required|max:255",
            "description"        => "required|max:4294967195",
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'email_message'      => 'required|max:4294967195',
            'veriant'            => 'required|array|min:1',
            'meta_title'         => 'nullable|string|max:255',
            'meta_description'   => 'nullable|string|max:255',
            'email_button_text'  => 'nullable|string',
            'email_button_url'   => 'nullable|url',
        ]);


        if ($validator->fails()) 
        {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }

        if($request->email_button_text != null && $request->email_button_url == null)
        {
            return response()->json([
                'success' => false,
                'message' => 'Please enter the button URL'
            ], 422);
        }

        if($request->email_button_text == null && $request->email_button_url != null)
        {
            return response()->json([
                'success' => false,
                'message' => 'Please enter the button text'
            ], 422);
        }

        if ($request->email_message == "<p><br></p>") {
            return response()->json([
                'success' => false,
                'message' => 'Email message is required'
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
    
            $product = Product::find($id);

            if (!$product) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            if ($product->title != $request->title) {
                $product->title          = $request->title;
                $product->slug           = Str::slug($request->title).'-'.time();
            }
            $product->description        = $request->description;
            $product->email_message      = $request->email_message;
            $product->user_id            = User::where('email', Auth::user()->email)->first()->id;
            $product->image              = $image_path ?? $product->image;
            $product->meta_title         = $request->meta_title;
            $product->meta_description   = $request->meta_description;
            $product->email_button_text  = $request->email_button_text ?? null;
            $product->email_button_url   = $request->email_button_url ?? null;
            $product->save();


            $old_veriants = [];

            foreach ($request->veriant as $key => $veriant) {

                if (!isset($veriant['variant_name']) || $veriant['variant_name'] == null) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Variant name is required'
                    ], 422);
                }

                if (!isset($veriant['variant_price']) || $veriant['variant_price'] == null) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Variant price is required'
                    ], 422);
                }

                if (!isset($veriant['product_type']) || $veriant['product_type'] == null) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Product type is required'
                    ], 422);
                }

                if (!isset($veriant['is_default']) || $veriant['is_default'] == null) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Is default is required'
                    ], 422);
                }

                if (!isset($veriant['variant_id']) || $veriant['variant_id'] == null) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Is default is required'
                    ], 422);
                }

                if(!isset($veriant['product_type']) || $veriant['product_type'] == null || ($veriant['product_type'] != 'Serial' && $veriant['product_type'] != 'File' && $veriant['product_type'] != 'Text' && $veriant['product_type'] != 'Webhook')) {

                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid product type'
                    ], 422);
                }


                if($veriant['variant_id'] != 0) {
                    

                    $product_variants = ProductVariants::find($veriant['variant_id']);
                    if (!$product_variants) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Product variant not found'
                        ], 404);
                    }

                    $old_veriants[] = $product_variants->id;

                    $product_variants->name = $veriant['variant_name'];
                    $product_variants->price = $veriant['variant_price'];
                    $product_variants->product_type = $veriant['product_type'];
                    $product_variants->is_default = $veriant['is_default'];
                    $product_variants->save();



                } else {

                    $product_variants = $product->variants()->create([
                        'name' => $veriant['variant_name'],
                        'price' => $veriant['variant_price'],
                        'product_type' => $veriant['product_type'],
                        'is_default' => $veriant['is_default']
                    ]);
                }


                if ($veriant['product_type'] == 'Serial') {

                    if (!isset($veriant['serial']) || $veriant['serial'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Serial is required'
                        ], 422);
                    }


                    if (!isset($veriant['serial_delimiter']) || $veriant['serial_delimiter'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Serial delimiter is required'
                        ], 422);
                    }


                    if (!isset($veriant['min_quantity']) || $veriant['min_quantity'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Minimum quantity is required'
                        ], 422);
                    }


                    if (!isset($veriant['max_quantity']) || $veriant['max_quantity'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Maximum quantity is required'
                        ], 422);
                    }
                    
                    ProductVariantSerial::where('product_variant_id', $product_variants->id)->delete();

                    $serials  = explode($veriant['serial_delimiter'] == '/n' ? PHP_EOL : $veriant['serial_delimiter'], $veriant['serial']);
                    $product_variants['serial_delimiter']  = $veriant['serial_delimiter'];

                    foreach ($serials as $serial) {
                        ProductVariantSerial::create([
                            'product_variant_id' => $product_variants->id,
                            'serial' => $serial
                        ]);
                    }

                    $product_variants['min_quantity']      = $veriant['min_quantity'];
                    $product_variants['max_quantity']      = $veriant['max_quantity'];
                    $product_variants->save();

                }elseif ($veriant['product_type'] == 'File') {

                    if (!isset($veriant['file']) || $veriant['file'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'File is required'
                        ], 422);
                    }

                    $file = $veriant['file'];
                    $filename = time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/files'), $filename);
                    $file_path = '/uploads/files/' . $filename;
                    $product_variants->file  = $file_path;
                    $product_variants->save();
         

                }elseif ($veriant['product_type'] == 'Text') {

                    if (!isset($veriant['text']) || $veriant['text'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Text is required'
                        ], 422);
                    }

                    $product_variants->text  = $veriant['text'];
                    $product_variants->save();

         
                }elseif ($veriant['product_type'] == 'Webhook') {

                    if (!isset($veriant['webhook']) || $veriant['webhook'] == null) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Webhook URL is required'
                        ], 422);
                    }

                    $product_variants->text  = $veriant['webhook'];
                    $product_variants->save();
                }

            }


            ProductVariants::where('product_id',$product->id)->whereNotIn('id',$old_veriants)->delete();

            

            $empty_serial = ProductVariantSerial::where('serial', '')->get();
            foreach ($empty_serial as $serial) {
                $serial->delete();
            }

            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }




    public function productOrdering(Request $request)
    {
        try {
            $order = $request->input('order');
            
            foreach ($order as $item) {
                Product::where('id', $item['id'])->update([
                    'position' => $item['position']
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }   



    public function variantEdit(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'product_type'       => 'required|in:Serial,File,Text,Webhook',
            'variant_name'       => 'required|max:255',
            'variant_price'      => 'required|numeric',
            'text'               => 'required_if:product_type,Text|max:255',
            'file'               => 'required_if:product_type,File|file|max:2048|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,png,jpg,jpeg',
            'serial'             => 'required_if:product_type,Serial',
            'min_quantity'       => 'required_if:product_type,Serial|numeric',
            'max_quantity'       => 'required_if:product_type,Serial|numeric',
            'serial_delimiter'   => 'required_if:product_type,Serial|max:255',
        ]);


        $product_variant = ProductVariants::findOrFail($id);
        $product_variant->name         = $request->variant_name;
        $product_variant->price        = $request->variant_price;
        $product_variant->product_type = $request->product_type;
        $product_variant->save();

        if ($request->product_type == 'Serial') {
            $product_variant->serial_delimiter  = $request->serial_delimiter;
            $product_variant->min_quantity      = $request->min_quantity;
            $product_variant->max_quantity      = $request->max_quantity;
            $product_variant->save();

            ProductVariantSerial::where('product_variant_id', $product_variant->id)->delete();
            $serials  = explode($request->serial_delimiter == '/n' ? PHP_EOL : $request->serial_delimiter, $request->serial);

            foreach ($serials as $serial) {
                ProductVariantSerial::create([
                    'product_variant_id' => $product_variant->id,
                    'serial'             => $serial
                ]);
            }


            if(notificationSetting() != null) {
                if(notificationSetting()->is_product_restock == 1) {
                    if(notificationSetting()->is_email_notification == 1) {

                        $users = User::where('role', 1)->get();
                        foreach ($users as $user) {           
                            // Mail::to($user->email)->queue(new ProductRestockEmail(
                            //     $product_variant->product->title,
                            //     $product_variant->product->image,
                            //     route('products.edit', $product_variant->product->id)
                            // ));

                            $body = view('emails.product_restock', ['title' => $product_variant->product->title, 'name' => $product_variant->product->image, 'url' => route('products.edit', $product_variant->product->id)])->render();
                            dispatch(new EmailJob($user->email, $request->subject, $user->name, $body));
                        }

                    }

                    if(notificationSetting()->is_discord_notification == 1 && notificationSetting()->discord_webhook_other != null) {
                        $message = "A product variant has been restocked. [View Product](".route('products.view', $product_variant->product->id).")";
                        sendDiscordMessage($message, 'Product Stock Update', 'A product variant stock has been updated. [View Product](' . route('products.view', $product_variant->product->id) . ')', '65280', notificationSetting()->discord_webhook_other);
                    }

                }
            }



        } elseif ($request->product_type == 'File') {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/files'), $filename);
                $file_path = '/uploads/files/' . $filename;
                $product_variant->file  = $file_path;
                $product_variant->save();
            }
        } elseif ($request->product_type == 'Text') {
            $product_variant->text  = $request->text;
            $product_variant->save();
        }elseif ($request->product_type == 'Webhook') {
            $product_variant->text  = $request->webhook;
            $product_variant->save();
        }

        $empty_serial = ProductVariantSerial::where('serial', '')->get();
        foreach ($empty_serial as $serial) {
            $serial->delete();
        }

        if ($request->product_type != 'Serial') {
            $empty_serial = ProductVariantSerial::where('product_variant_id', $product_variant->id)->get();
            foreach ($empty_serial as $serial) {
                $serial->delete();
            }
        }


        return redirect()->back()->with('success', 'Product variant updated successfully');
    }




    public function variantDelete($id){

        $product_variant = ProductVariants::findOrFail($id);
        $product_variant->delete();

        return redirect()->back()->with('success', 'Product variant deleted successfully');
    }




    public function destroy($id){

        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success','Product deleted successfully');
        
    }


    public function status($id){

        $product = Product::findOrFail($id);
        $product->show_status = !$product->show_status;
        $product->save();

        return redirect()->route('products.index')->with('success','Product deleted successfully');
        
    }
}
