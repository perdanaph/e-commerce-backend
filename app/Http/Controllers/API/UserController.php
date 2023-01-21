<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function register(Request $request) {
        try {
            $request -> validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'phone' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'string', new Password]
            ]);

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
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Fail', 500);
        }
    }
}
