<?php

namespace App\Console\Commands;

use App\Mail\AdminOrderDeliveryMail;
use App\Mail\DeliveryMail;
use App\Mail\ProductStockOutMail;
use App\Models\CryptoPayments;
use App\Models\CryptoSettings;
use App\Models\Order;
use App\Models\ProductSerial;
use App\Models\ProductVariantSerial;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Jobs\EmailJob;

class CheckCryptoPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-crypto-payment';

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

            $crypto_payments = CryptoPayments::where('payment_status', '!=','finished')->get();

            $setting = CryptoSettings::first();

            

            if ($setting != null) {
                foreach ($crypto_payments as $key => $crypto_payment) {
                    
                    $response = Http::withHeaders([
                        'x-api-key' => $setting->api_key,
                    ])->get('https://api.nowpayments.io/v1/payment/'.$crypto_payment->payment_id);

                     

                    if ($response->successful()) {

                        $crypto_payment->payment_status = $response->json()['payment_status'];
                        $crypto_payment->save();

                        $custom_status = 0;
                        if ($response->json()['payment_status'] == 'partially_paid') {
                           $less_paid =  $crypto_payment->pay_amount - $response->json()['actually_paid'];
                           $less_percentage = ($less_paid / $crypto_payment->pay_amount) * 100;
                            if ($less_percentage < 5) {
                                $custom_status = 1;
                            }
                        }

                        if ($response->json()['payment_status'] == 'finished' || ($response->json()['payment_status'] == 'partially_paid' && $custom_status == 1)) {
                        
                            $crypto_payment->payment_status = 'finished';
                            $crypto_payment->save();
                            
                            $order = Order::find($crypto_payment->order_id);
                            // $order->payment_status = 1;
                            // $order->save();
                            
    
                            if ($order->variant->product_type == 'File') {
                                $product_delivery = $order->variant->file;
                            }elseif ($order->variant->product_type  == 'Text') {
                                $product_delivery = $order->variant->text;
                            }elseif ($order->variant->product_type  == 'Webhook') {
                                $data = Http::get($order->variant->text);
                                $product_delivery = $data->body();
                            }elseif ($order->variant->product_type  == 'Serial') {

                                $product_delivery = [];
                
                                for($i = 1;  $i <= $order->quantity; $i++) {
                
                                    $product_serial = ProductVariantSerial::where('product_variant_id', $order->product_variant_id)->first();
                                    // dd($order->product->id);
                                    $product_delivery[] = $product_serial->serial;
                                    $product_serial->delete();
                                }
            
                                if(ProductVariantSerial::where('product_variant_id', $order->product_variant_id)->count() == 0) 
                                {
                                    if(notificationSetting() != null) {
                                        if(notificationSetting()->is_product_out_of_stock == 1) {
                                            if(notificationSetting()->is_email_notification == 1) {
                                                $users = User::where('role', 1)->get();
                                                foreach ($users as $user) {           
                                                    // Mail::to($user->email)->queue(new ProductStockOutMail(
                                                    //     $order->product->title,
                                                    //     $order->product->image,
                                                    //     route('products.edit', $order->product->id)
                                                    // ));

                                                    $body = view('emails.product_stock_out', ['title' => $order->product->title, 'image' => $order->product->image, 'url' => route('products.edit', $order->product->id)])->render();
                                                    dispatch(new EmailJob($order->email, "Product Stock Out", $order->name, $body));
                                                }
                                            }
                    
                                            if(notificationSetting()->is_discord_notification == 1) {
                                                $message = "A Product is out of stock. [View Product](".route('products.edit', $order->product->id).")";
                                                sendDiscordMessage($message, 'Product Out of Stock', 'A Product is out of stock. [View Product]('.route('products.edit', $order->product->id) . ')', '16711680',notificationSetting()->discord_webhook);
                                            }
                    
                                        }
                                    }
                                }
            
                            }
                

                
                            Mail::to($order->email)->queue(new DeliveryMail(
                                $product_delivery,
                                $order->uuid,
                                $order->sub_total,
                                $order->quantity,
                                $order->discount,
                                $order->total,
                                $order->product->image,
                                $order->product->email_message,
                                $order->variant->product_type,
                                $order->product->title,
                                'https://'.$tenant->id.'.sellhub.io/order-invoice-download/'.$order->uuid,
                                $order->product->email_button_text,
                                $order->product->email_button_url,
                            ));
            
                   
            
                            if(notificationSetting() != null) {
                                if(notificationSetting()->is_order_delivery == 1) {
                                    if(notificationSetting()->is_email_notification == 1) {
                                        $users = User::where('role', 1)->get();
                                        foreach ($users as $user) {           
                                            Mail::to($user->email)->queue(new AdminOrderDeliveryMail(
                                                $order->uuid,
                                                route('orders.index')
                                            ));
                                        }
                                    }
            
                                    if(notificationSetting()->is_discord_notification == 1) {
                                        $message = "You have a new order completed successfully. [View Order](".route('orders.index').")";
                                        $description  = "**Product Name**\n";
                                        $description .= $order->product->title."\n\n";
                                        $description .= "**Variant Name**\n";
                                        $description .= $order->variant_name."\n\n";
                                        $description .= "**Customer Name**\n";
                                        $description .= $order->name."\n\n";
                                        $description .= "**Customer email**\n";
                                        $description .= $order->email."\n\n";
                                        $description .= "**Payment gateway**\n";
                                        $description .= $order->payment_method."\n\n";
                                        $description .= "**Total**\n";
                                        $description .= "$".$order->total."\n\n";
                                        $description .= "**Invoice ID**\n";
                                        $description .= $order->uuid."\n\n";
                                    
                                        sendDiscordMessage($message, 'You have a new order of $'.$order->total , $description, '65280',notificationSetting()->discord_webhook);
                                    }
            
                                }
                            }

                            $order->payment_status = 1;
                            $order->product_type = $order->variant->product_type;

                            if($order->variant->product_type == 'Serial') {
                                $order->product_value = json_encode($product_delivery);
                            }else {
                                $order->product_value = $product_delivery;
                            }
                            $order->save();
                        }
                    }
                }
            }
        }
    }
}
