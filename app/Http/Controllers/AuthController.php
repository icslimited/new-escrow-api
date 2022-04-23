<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;

class AuthController extends Controller
{
    //
    public function register(Request $request){
        try {
            $fields = $request->validate([
                'email' => 'required|string|email|unique:users,email',
                'phone_number' => 'required|string',
                'password' => 'required|string|confirmed|min:7'
            ]);
    
            $user = User::create([
                'email' => $fields['email'],
                'phone_number' => $fields['phone_number'],
                'password' => bcrypt($fields['password'])
            ]);
    
            // Create access token
            // $token = $user->createToken('authToken')->plainTextToken;
    
            // return response
            return response()->json(['success' => true, 'user'=>$user], 201);
        } catch (Exception $ex) {
            return response()->json(['success' => false, 'error'], 500);
        }
    }

    //
    public function login(Request $request){
        try {
            $fields = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string|min:7'
            ]);

            if(!Auth::attempt($fields)){
                return $this->error('Credentials not match', 401);
            }

            $user = User::where('email', $fields['email'])->first();
            $token = auth()->user()->createToken('authToken')->plainTextToken;

            return response()->json(['success' => true, 'user' => $user, 'token' => $token], 200);
        } catch (Exception $ex) {
            return response()->json(['success' => false, 'errors' => array('message' => $ex.getMessage()) ], 500);
        }
    }

    //
    public function logout(){
        try {
            auth()->user()->tokens()->delete();

            return response()->json(['success' => true], 200);
        } catch (Exception $ex) {
            return response()->json(['success' => false, 'errors' => array('message' => $ex.getMessage()) ], 500);
        }
    }
    
}
