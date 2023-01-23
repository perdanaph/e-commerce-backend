<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function register(Request $request) {
        $request -> validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', new Password]
        ]);
        try {
            User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'username'=>$request->username,
                'phone'=>$request->phone,
                'password'=> Hash::make($request->password),
            ]);

            $user =  User::where('email', $request->email)->first();
            $token = $user->createToken('authToken')->plainTextToken;

            return ResponFormatter::success([
                'token'=>$token,
                'type_token'=>'Bearer ',
                'data'=>$user,
            ], 'Success Registered', 201);
        } catch (Exception $error) {
            return ResponFormatter::error([
                'message' => 'Internal Server Error',
                'error' => $error,
            ], 'Error', 500);
        }
    }

    public function login(Request $request) {
        $request->validate([
            'email'=>'email|required',
            'password'=>'required'
        ]);
        try {
            $user = User::where('email', $request->email)->first();
            if(!$user) {
                return ResponFormatter::error([
                    'message'=>'cannot find your email'
                ], 'Unauthorized', 400);
            }

            if (!Hash::check($request->password, $user->password, [])) {
                return ResponFormatter::error([
                    'message'=>'Wrong password',
                ], 'Unauthorized', 400);
            }
            
            $token = $user->createToken('authToken')->plainTextToken;
            return ResponFormatter::success([
                'token' => $token,
                'token type' => 'Bearer ',
                'user' => $user
            ], 'Success Authentication');
        } catch (Exception $error) {
            return ResponFormatter::error([
                'message'=> 'Internal Server Error',
                'error'=>$error
            ], 'Internal Server Error', 500);
        }
    }

    public function fetch(Request $request) {
        return ResponFormatter::success($request->user(), 'Success to get data Users');
    }

    public function update(Request $request) {
        $request -> validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);
        try {
            $data = $request->all();
            $user = Auth::user();
            $user->update($data);

            return ResponFormatter::success([
                $user
            ], 'Profile Success Updated');

        } catch (Exception $error) {
            return ResponFormatter::error([
                'message'=> 'Internal Server Error',
                'error' => $error
            ], 'Internal Server Error', 500);
        }
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return ResponFormatter::success([
            'message'=> 'Token Has success Revoked'
        ], 'Logout Success', 200);
    }
}
