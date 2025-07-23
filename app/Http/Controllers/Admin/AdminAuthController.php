<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                                        'chemist_admin_token' => $token,
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
                'chemist_refresh_token',
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
        if (request()->cookie('chemist_refresh_token')) {

            $payload = JWTAuth::manager()->getJWTProvider()->decode(request()->cookie('chemist_refresh_token'));

            
            if (array_key_exists("refresh_token", $payload) && $payload['refresh_token']) {
                $user = JWTAuth::setToken(request()->cookie('chemist_refresh_token'))->toUser();
                if ($user) {
                    return response()->json([
                        'chemist_admin_token' => JWTAuth::fromUser($user),
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

            JWTAuth::manager()->invalidate(new Token(request()->cookie('chemist_refresh_token')), true);

            return response()->json([
                'status'    => true,
            ])->cookie('chemist_refresh_token', null, 43200, null, null, true, true );

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'errors'  => ['Unauthorized']
            ],401);
        }




    }
}
