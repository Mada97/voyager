<?php

namespace App\Http\Controllers\Api;

use Laravel\Passport\HasApiTokens;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                'avatar' => ['nullable', 'file', 'image']
            ]
        );
        if ($validator->fails())
        {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $input = $request->all();
        // uploading profile picture.
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            if (!$file->isValid()) {
                return response()->json(['invalid profile picture.'], 400);
            }
            $path = public_path('/uploads/avatars/');
            $file->move($path, $file->getClientOriginalName());
            $avatar = 'uploads/avatars/' . $file->getClientOriginalName();
            $input['avatar'] = $avatar;
        }
        /* XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX */

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
            return response()->json(['error' => 'Wrong Credentials'], 401);
        }
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if($request['email'] == $user->email) {
            $request->request->remove('email');
        }
        if($request['phone_number'] == $user->phone_number) {
            $request->request->remove('phone_number');
        }
        if(empty($request['name'])) {
            $request->request->remove('name');
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['nullable', 'string', 'max:255'],
                'email' => ['string', 'email', 'max:255', 'unique:users'],
                'phone_number' => ['nullable', 'regex:/(01)[0-9]{9}/', 'size:11', 'unique:users'],
                'password' => ['string', 'min:8', 'confirmed'],
                'avatar' => ['nullable', 'file', 'image']
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }


        $input = $request->all();

        // uploading profile picture.
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            if (!$file->isValid()) {
                return response()->json(['invalid profile picture.'], 400);
            }
            $path = public_path('/uploads/avatars/');
            $file->move($path, $file->getClientOriginalName());
            $avatar = 'uploads/avatars/' . $file->getClientOriginalName();
            $input['avatar'] = $avatar;
        }
        /* XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX */

        $input['password'] = bcrypt($input['password']);
        $user->update($input);
        $user->avatar =asset($user->avatar);

        return response()->json(['success' => true, 'message' => "Your information has been updated", 'data' => $user]);
    }

    // Get user details
    public function getUser()
    {
        $user = Auth::user();
        $user->avatar = asset($user->avatar);
        $user['trips'] = $user->trips;
        return response()->json(['success' => $user], $this->successStatus);
    }

    // User Profile
    public function profile(User $user) {
        $user->avatar = asset($user->avatar);
        return response()->json(['User Data' => $user], $this->successStatus);
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
