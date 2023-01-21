<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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

    public function login(Request $request) {
        try {
            $request->validate([
                'email'=>'email|required',
                'password'=>'required'
            ]);

            $credentials = request(['email', 'password']);
            
            $user = User::where('email', $request->email)->first();
            
            if (!Hash::check($request->password, $user->password, [])) {
                return ResponFormatter::error([
                    'message' => 'Wrong Password',
                    
                ], 'Fail', 400);
            }
            if (!Auth::attempt($credentials)) {
                return ResponFormatter::error([
                    'message' => 'Field Email & Password Requeired',
                    
                ], 'Fail, BadRequest', 400);
            }
            $token = $user->createToken('authToken')->plainTextToken;
            return ResponFormatter::success([
                'token' => $token,
                'token type' => 'Bearer ',
                'user' => $user
            ], 'Success Authentication');
        } catch (Exception $error) {
            return ResponFormatter::error([
                'message'=>'Internal Server Error',
                'error'=>$error
            ], 'Failed', 500);
        }
    }

    public function fetch(Request $request) {
        return ResponFormatter::success($request->user(), 'Success to get data Users');
    }

    public function update(Request $request) {
        try {
            // $validation = Validator::make($request->all(), [
            //     'name' => 'required',
            //     'email' => 'required | email',
            //     'username' => 'required',
            //     'phone' => 'nullable'
            // ]);

            // if ($validation->fails()) {
            //     return ResponFormatter::error([
            //     ], 'name, email, username canot be omitted', 400);
            // }

            $data = $request->all();
            $user = Auth::user();
            $user->update($data);

            return ResponFormatter::success([
                $user
            ], 'Profile Success Updated');

        } catch (Exception $error) {
            return ResponFormatter::error([
                'error' => $error
            ], 'Internal Server Error', 500);
        }
    }
}
