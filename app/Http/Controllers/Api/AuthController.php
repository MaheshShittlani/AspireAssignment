<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        $request['password'] = Hash::make($request['password']);
        $user = User::create($request->all());
        $token = $user->createToken('userAuth')->accessToken;
        return response()->json(['msg' => 'User Created Successfully','data'=>['user' => $user,'token' => $token]],201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);   

        if($validator->fails()) {
            response()->json(['errors' => $validator->errors()],422);
        }

        $user = User::where('email',$request->email)->first();

        if($user) {
            if(Hash::check($request->password,$user ->password)) {
                $token = $user->createToken('userAuth')->accessToken;
                return response()->json(['msg' => 'Login Successful','data' => ['user' => $user,'token' => $token]]);
            }else {
                return response()->json(['error' => 'Incorrect Password, Try Again.'],401);
            }
        }
    }

    public function logout(Request $request)
    {
        return $token = $request->user();
        $token->revoke();
        return response()->json(['msg' => 'Logout Successfull'],200);
    }
}
