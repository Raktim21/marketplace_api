<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserEmailOtp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class AdminAuthController extends Controller
{
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
                                        'admin_token' => $token,
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
                'admin_refresh_token',
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
        if (request()->cookie('admin_refresh_token')) {

            $payload = JWTAuth::manager()->getJWTProvider()->decode(request()->cookie('admin_refresh_token'));

            
            if (array_key_exists("admin_refresh_token", $payload) && $payload['admin_refresh_token']) {
                $user = JWTAuth::setToken(request()->cookie('admin_refresh_token'))->toUser();
                if ($user) {
                    return response()->json([
                        'admin_token' => JWTAuth::fromUser($user),
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

            JWTAuth::manager()->invalidate(new Token(request()->cookie('admin_refresh_token')), true);

            return response()->json([
                'status'    => true,
            ])->cookie('admin_refresh_token', null, 43200, null, null, true, true );

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'errors'  => ['Unauthorized']
            ],401);
        }




    }





    public function profile()
    {
        $user          = User::where('email', Auth::user()->email)->first();
        $product_count = Product::where('user_id', $user->id)->count();
        $order_count   = Order::where('payment_status', 1)->count();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'status' => true,
            'data' => $user,
            'product_count' => $product_count,
            'order_count' => $order_count
        ]);
    }



    public function emailOtp()
    {

        $otp = UserEmailOtp::create([
            'email' => Auth::user()->email,
            'otp'   => mt_rand(100000, 999999),
        ]);


        // Mail::to(Auth::user()->email)->send(new VerifyEmailMail(
        //     $otp->otp,
        // ));

        $body = view('emails.email_verification', ['otp' => $otp->otp])->render();
        noReplayMail(Auth::user()->email, 'Email Verification', Auth::user()->name, $body);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your email.',
            'otp' => $otp->otp
        ]);

    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required',
        ]);


        $otp = UserEmailOtp::where('email', Auth::user()->email)->latest()->first();

        if ($otp->otp == $request->otp) {
            // UserEmailOtp::where('email', Auth::user()->email)->delete();
            return response()->json([
                'status'  => true,
                'message' => 'Email verified successfully.',
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP. Please try again.'
        ], 400);
    }





    public function profileInfoUpdate(Request $request)
    {
        $user = User::where('email', Auth::user()->email)->first();

        if ($user == null) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'email' =>  'required|email|unique:users,email,'.$user->id,
            'name'  =>  'required|min:3|max:255',
            'phone' =>  'string|min:3|max:255',
            'otp'   =>  'required'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        // if ($request->hasFile('image')) {
        //     $image = $request->file('image');
        //     $imagename = time() . '.' . $image->getClientOriginalExtension();
        //     $image->move(public_path('uploads/products/images'), $imagename);
        //     $image_path = '/uploads/user/images/' . $imagename;
        //     $user->image = $image_path;
        // }



        $otp = UserEmailOtp::where('email', Auth::user()->email)->latest()->first();

        if ($otp->otp != $request->otp) {
            UserEmailOtp::where('email', Auth::user()->email)->delete();
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP. Please try again.'
            ], 400);
        }



        $user->name  = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        DB::table(env('CENTRAL_DB').'.users')
            ->where('email', Auth::user()->email)
            ->update([
                'name'  => $request->name,
                'email' => $request->email
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully.',
        ]);
    }


    public function profilePicUpdate(Request $request)
    {
        $user = User::where('email', Auth::user()->email)->first();

        if ($user == null) {
            return redirect()->back()->with('error', 'User not found');
        }

        $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);


        $image = $request->file('image');
        $imagename = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/user/images'), $imagename);
        $image_path = '/uploads/user/images/' . $imagename;
        $user->image = $image_path;
        $user->save();

        return redirect()->back()->with('success', 'Image updated successfully');
    }
    

    public function profilePassUpdate(Request $request)
    {
        $user = User::where('email', Auth::user()->email)->first();

        $request->validate([
            'current_password'      =>  'required',
            'password'              => 'required|confirmed|min:6|',
            'password_confirmation' => 'required|same:password',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            
            return back()->with('error', 'Your current password does not match');
        }

        $user->password = Hash::make($request->password);
        $user->save();



        DB::table(env('CENTRAL_DB').'.users')
        ->where('email', Auth::user()->email)
        ->update([
            'password'  =>  Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully');
    }



}
