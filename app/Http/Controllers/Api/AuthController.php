<?php

namespace App\Http\Controllers\Api;

use Laravel\Passport\HasApiTokens;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use HasApiTokens;
    public $successStatus = 200;

    public function register(Request $request)
    {
        $validator = Validator::make
        (
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone_number' => ['nullable', 'regex:/(01)[0-9]{9}/', 'size:11', 'unique:users'],
                'gender' => ['required', 'string'],
                'date_of_birth' => ['required', 'date'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]
        );
        if ($validator->fails())
        {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $success['token'] =  $user->createToken('AppName')->accessToken;
        return response()->json([
            'success' => 'true',
            'token' => $success,
            'user' => $user
        ], $this->successStatus);
    }

    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')]))
        {
            $user = Auth::user();
            $success['token'] =  $user->createToken('AppName')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        } else
        {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    // Get user details
    public function getUser()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    // Logout from current device
    public function logout()
    {
        if(Auth::user()) {
            Auth::user()->token()->revoke();
            return response()->json([
                'success' => true,
                'message' => 'Successfuly logged out.'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unable to logout.'
            ]);
        }
    }

    // Logout from all connected devices
    public function logoutFromAllDevices()
    {
        DB::table('oauth_access_tokens')
            ->where('user_id', Auth::user()->id)
            ->update([
                'revoked' => true
            ]);
        return response()->json(['message' => 'Successfuly logged out from all devices.'], 200);
    }


}
