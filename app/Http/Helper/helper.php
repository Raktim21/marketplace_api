
<?php
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\EmailSettings;
use App\Models\GeneralSetting;
use App\Models\GroupProduct;
use App\Models\NotificationSetting;
use App\Models\Order;
use App\Models\PaymentGateways;
use App\Models\Product;
use App\Models\ProductVariants;
use App\Models\ProductVariantSerial;
use App\Models\PromoCode;
use App\Models\SeoSetting;
use App\Models\TitleSetting;
use App\Models\User;
use App\Models\UserRating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PHPMailer\PHPMailer\PHPMailer;

    function settings(){
        $settings = GeneralSetting::first();
        return $settings;
    }


    function titleSettings(){
        
        $settings = TitleSetting::first();
        return $settings;
    }


    function seoSettings(){
        $settings = SeoSetting::first();
        return $settings;
    }


    function sellerTotalSell($tenant){
        $settings = DB::table('tenant'.$tenant.'.orders')->where('payment_status', 1)->sum('total');
        return $settings;
    }


    function checkGroup($group_id, $product_id)
    {
        $group = GroupProduct::where('group_id', $group_id)->where('product_id', $product_id)->first();
        if ($group) {
            return true;
        }
        return false;
    }

    function groupProductLowestPrice($group_id)
    {
        return GroupProduct::where('group_id', $group_id)
                        ->join('products', 'group_products.product_id', '=', 'products.id')
                        ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                        ->min('product_variants.price');
    }


    function PaymentGateway($name)
    {
        return PaymentGateways::where('name', $name)->first();
    }


    function totalOrder()
    {
        return Order::where('payment_status', 1)->count();
    }


    function averageRating()
    {
        return UserRating::avg('rating');
    }

    function totalCart()
    {
        return Cart::where('ip_address', request()->ip())->count();
    }


    function notificationSetting()
    {
        return NotificationSetting::first();
    }


    function sendDiscordMessage($message , $title , $description , $color, $webhookUrl) {
        try {
            Http::post($webhookUrl, [
                // 'content' => $message,
                'embeds' => [
                    [
                        'title' => $title,
                        'description' => $description,
                        'color' => $color, // Green color
                        // 'footer' => [
                        //     'text' => 'Sellhub APP',
                        //     'icon_url' => 'https://example.com/your-logo.png' // Add your logo URL
                        // ],
                        'timestamp' => now()->toIso8601String()
                    ]
                ],
            ]);

            return true;
        } catch (\Throwable $th) {
            
            return false;
        }
    }


    function checkPermission($permission)
    {
        $user = User::where('email', Auth::user()->email)->first();

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->hasAllPermissions($permission)) {
            return true;
        }else {
            return false;
        }
    }


    function getGeolocation($ip)
    {
        $response = Http::get("http://ip-api.com/json/{$ip}");

        if ($response->successful()) {
            $data = $response->json();
            return $data['country'] ?? 'United Arab Emirates';
        }

        return 'United Arab Emirates';
    }


    function orderNoteGenerate()
    {
        $templates = [
            ':adj :noun :verb',      // e.g. "brave child runs"
            ':noun :verb :adj',      // e.g. "robot builds smart"
            ':verb the :noun',       // e.g. "explore the world"
            'be :adj :noun',         // e.g. "be happy coder"
            ':adj and :adj',         // e.g. "brave and curious"
        ];

        $adjectives = ['brave', 'curious', 'happy', 'lonely', 'wise', 'bold'];
        $nouns = ['developer', 'cat', 'robot', 'child', 'teacher', 'coder'];
        $verbs = ['learn', 'run', 'build', 'create', 'explore', 'think'];

        $template = $templates[array_rand($templates)];

        $replacements = [
            ':adj' => fn() => $adjectives[array_rand($adjectives)],
            ':noun' => fn() => $nouns[array_rand($nouns)],
            ':verb' => fn() => $verbs[array_rand($verbs)],
        ];

        // Replace placeholders with words
        $sentence = preg_replace_callback('/:adj|:noun|:verb/', function ($matches) use ($replacements) {
            return $replacements[$matches[0]]();
        }, $template);

        // Optional: Add short unique suffix
        $suffix = '-' . Str::random(3); // Optional, for uniqueness
        return $sentence . $suffix;
    }


    function totalOrders($email)
    {
        return Order::where('email', $email)->where('payment_status', 1)->count();
    }

    function totalOrdersAmount($email)
    {
        return Order::where('email', $email)->where('payment_status', 1)->sum('total');
    }


    function productStockStatus($product_id)
    {
        $check_inventory = ProductVariants::where('product_id', $product_id)->get();

        if ($check_inventory->isEmpty()) {
           return 0;
        }else {
            $check_serial = ProductVariants::where('product_id', $product_id)->where('product_type', 'Serial')->get();
            $status = 0;
            if ($check_serial->isEmpty()) {
                $status = 2;
            }else {
                $status = 1;
                foreach ($check_serial as $serial) {
                    if ($serial->serials->count() < 1) {
                        $status = 0;
                        break;
                    }
                }
            }
        }

        return $status;
    }



    function promocodeDiscount($promo_code,$variant_id)
    {
        $promo = PromoCode::where('code', $promo_code)->first();

        if (!$promo) {
            return 0;
        }

        $order = Order::where('payment_status', 1)->where('promo_code_id', $promo->id)->count();


        if ($order >= $promo->max_number_uses) {
            return 0;
        }

        if ($promo->expires_at < now()) {
            return 0;
        }

        $variant = ProductVariants::findOrFail($variant_id);


        
        if ($promo->is_global_product == 0) {
            if ($promo->product_id != $variant->product_id) {
                return 0;
            }
        } 

        $price = $variant->price;

       

        if ($promo->is_percent == 0) {
            if($promo->amount > $price){
                return 0;
            }

            $discount = $promo->amount;

            return $discount;
        }
        else{
            $discount = ($price * $promo->amount) / 100;

            return $discount;
        }


    }


    function authUser(){
        return User::where('email', Auth::user()->email)->first();
    }


    function noReplayMail($email , $subject , $name, $body) {

        // $mail = new PHPMailer(true);
        try {
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST_2', 'smtp.mailgun.org');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME_2');
            $mail->Password   = env('MAIL_PASSWORD_2');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION_2', 'ssl');
            $mail->Port       = env('MAIL_PORT_2', 465);
            // $mail->SMTPDebug  = 2; // Enable verbose debug output
            // $mail->Debugoutput = function($str, $level) {
            //     Log::info("PHPMailer: $str");
            // };
            $mail->setFrom('noreply@sellhub.io', $subject);
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();

            
            return true;

        } catch (\Throwable $th) {
            // dd($th->getMessage());
            return false;

        }
        
    }

?>