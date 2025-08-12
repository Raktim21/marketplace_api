<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserEmailOtp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => 'required|string|email|max:255',
            'password'              => ['required', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required'],
            'country'               => ['required', 'string', 'max:255'],
            'subdomain'             => [
                                            'required',
                                            'string',
                                            'max:255',
                                            'unique:sellers,subdomain',
                                            'regex:/^[a-zA-Z0-9]+$/',
                                        ],

        ],[
            'subdomain.regex' => 'The subdomain only allows letters no space or special characters.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors'  => $validator->errors()->all()
            ], 422);
        }


        DB::beginTransaction();

        try {
            $user = User::where('email', $request->email)->first();

            if ($user != null) {
                if ($user->email_verified_at == null) {
                    if ($user->tenant_id != null) {
                        $user->name       = $request->name;
                        $user->password   = Hash::make($request->password);
                        $user->tenant_id  = $request->subdomain;
                        $user->country    = $request->country;
                        $user->save();
                    }else {
                        return response()->json([
                            'status' => false,
                            'errors'  => ['Email already exists']
                        ], 422);
                    }
                }else {
                    return response()->json([
                        'status' => false,
                        'errors'  => ['Email already exists']
                    ], 422);
                }
            }else {
                
                User::create([
                    'name'      => $request->name,
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password),
                    'tenant_id' => $request->subdomain,
                    'country'   => $request->country
                ]);
            }

            UserEmailOtp::where('email', $request->email)->delete();

            $otp =  UserEmailOtp::create([
                        'email' => $request->email,
                        'otp'   => rand(100000, 999999)
                    ]);

            // Mail::to($otp->email)->send(new VerifyEmailMail(
            //     $otp->otp,
            // ));

            // $body = view('emails.email_verification', ['otp' => $otp->otp])->render();
            // noReplayMail($otp->email, 'Email Verification', $request->name, $body);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'OTP has been sent to your email.',
                'otp' => $otp->otp,
                'email' => $otp->email
            ], 200);

        } catch (\Throwable $th) {
            
            DB::rollBack();

            return response()->json([
                'status' => false,
                'errors'  => ['Something went wrong']
            ], 422);
        }
    }



    public function registerEmailVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $user_otp = UserEmailOtp::where('email', $request->email)->latest()->first();

        if (!$user_otp || $user_otp->otp != $request->otp) {
            return response()->json([
                'status' => false,
                'errors' => ['Invalid OTP']
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::where('email', $user_otp->email)->firstOrFail();
            $user->email_verified_at = now();
            $user->save();
            $user_otp->delete();

            // Create seller
            Seller::create([
                'name'      => $user->name,
                'email'     => $user->email,
                'subdomain' => $user->tenant_id,
                'country'   => $user->country
            ]);

            // Create tenant - database is automatically handled
            $tenant = Tenant::create([
                'id' => $user->tenant_id,
            ]);

            // $tenant->createDatabase();
            
            // Create domain
            $tenant->domains()->create([
                'domain' => $user->tenant_id . '.' . env('CENTRAL_DOMAIN')
            ]);

            // Initialize tenancy (creates and connects to database)
            tenancy()->initialize($tenant);

            // Run migrations
            $migrationExitCode = Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force' => true,
            ]);

            if ($migrationExitCode !== 0) {
                throw new \RuntimeException('Tenant migration failed');
            }

            // Run seeders
            Artisan::call('tenants:seed', [
                '--class' => 'DatabaseSeeder',
                '--tenants' => [$tenant->id],
                '--force' => true,
            ]);

            // Additional seeders if needed
            Artisan::call('tenants:seed', [
                '--class' => 'PaymentSeeder',
                '--tenants' => [$tenant->id],
                '--force' => true,
            ]);

            Artisan::call('tenants:seed', [
                '--class' => 'RolePermissionSeeder',
                '--tenants' => [$tenant->id],
                '--force' => true,
            ]);

            DB::table('tenant'.$user->tenant_id .'.general_settings')->insert([
                'name'    => "Sellhub",
                'address' => "Address",
                'email'   => $user->tenant_id,
                'phone'   => "0120000000",
                'logo'    => "Sellhub",
                'favicon' => "Sellhub",
            ]);

            DB::table('tenant'.$user->tenant_id .'.users')->insert([
                'name'     => $user->name,
                'email'    => $user->email,
                'password' => $user->password,
                'role'     => 1,
                'country'  => $user->country
            ]);


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Tenant created successfully',
                'domain' => $user->tenant_id . '.' . env('CENTRAL_DOMAIN')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Tenant creation failed: {$e->getMessage()}\n{$e->getTraceAsString()}");
            return response()->json([
                'status' => false,
                'errors' => ['Tenant creation failed'],
                'debug' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }



    public function login(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->all()
            ], 422);
        }

        $credentials = array((filter_var($request->get('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone') => $request->get('email'), 'password' => $request->get('password'));

        if ($token = Auth::attempt($credentials)) {

            $user = Auth::user();

            $data = array(
                        'user'  =>  $user,
                        'token' =>  array(
                                        'seller_token' => $token,
                                        'token_type'          => 'bearer',
                                        'expires_in'          => Auth::factory()->getTTL() * 60
                                    )
                    );


            $response = response()->json([
                'status' => true,
                'data' => $data,
            ]);

            $token = JWTAuth::fromUser($user);

            // Set expiration for the refresh token (1 month)
            $expiration = Carbon::now()->addMonth();

            // Create a refresh token with custom claims and expiration
            $refreshToken = JWTAuth::claims([
                'exp'           => $expiration->timestamp,
                'refresh_token' => true,
            ])->fromUser($user);


            $response->cookie(
                'seller_refresh_token',
                $refreshToken,
                43200,
                '/',  
                null, 
                true, 
                true, 
                false, 
                'None', 
            );

            return $response;

        } else {

            return response()->json([
                'status' => false,
                'errors' => ['Unauthorized user.']
            ], 401);
        }
    }


    public function me()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'status' => true,
            'data' => $user,
        ]);
    }


    public function refresh()
    {
        if (request()->cookie('seller_refresh_token')) {

            $payload = JWTAuth::manager()->getJWTProvider()->decode(request()->cookie('seller_refresh_token'));

            
            if (array_key_exists("seller_refresh_token", $payload) && $payload['seller_refresh_token']) {
                $user = JWTAuth::setToken(request()->cookie('seller_refresh_token'))->toUser();
                if ($user) {
                    return response()->json([
                        'seller_token' => JWTAuth::fromUser($user),
                        'token_type'          => 'bearer',
                        'expires_in'          => Auth::factory()->getTTL() * 60
                    ]);
                }
            }

        }

        return response()->json([
            'status'  => false,
            'error'   => 'Unauthorized'
        ], 401);
    }


    public function logout()
    {
        try {
            Auth::logout();

            JWTAuth::manager()->invalidate(new Token(request()->cookie('seller_refresh_token')), true);

            return response()->json([
                'status'    => true,
            ])->cookie('seller_refresh_token', null, 43200, null, null, true, true );

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'errors'  => ['Unauthorized']
            ],401);
        }




    }









    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $seller = Seller::where('email', request()->email)->first();
      

        if ($seller == null) {
            return response()->json([
                'status' => false,
                'errors' => ['User not found']
            ], 422);
        }

        UserEmailOtp::where('email', request()->email)->delete();

        $otp = UserEmailOtp::create([
            'email' => request()->email,
            'otp'   => mt_rand(100000, 999999),
        ]);


        // Mail::to(request()->email)->send(new VerifyEmailMail(
        //     $otp->otp,
        // ));

        $body = view('emails.email_verification', ['otp' => $otp->otp])->render();
        noReplayMail($otp->email, 'Email Verification', $seller->name, $body);

        return response()->json([
            'status' => true,
            'message' => 'OTP has been sent to your email.',
            'otp' => $otp->otp,
            'email' => $otp->email
        ], 200);
    }



    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $user_otp = UserEmailOtp::where('email', $request->email)->latest()->first();

        if (!$user_otp || $user_otp->otp != $request->otp) {
            return response()->json([
                'status' => false,
                'errors' => ['Invalid OTP']
            ], 422);
        }

        // $user_otp->delete();

        return response()->json([
            'status'  => true,
            'message' => 'OTP verified successfully',
            'email'   => $user_otp->email
        ]);
    }


    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp'                   => 'required|numeric',
            'password'              => ['required', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }


        $user_otp = UserEmailOtp::where('otp', $request->otp)->latest()->first();

        if (!$user_otp) {
            return response()->json([
                'status' => false,
                'errors' => ['Invalid OTP']
            ], 422);
        }


        $user = User::where('email', $user_otp->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'errors' => ['User not found']
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $user_otp->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Password reset successfully'
        ] , 200);
    }
}
