<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Notifications\UserNotification;
use App\Models\User;

class AuthController extends Controller
{
    //
    public function register(Request $request){
        try {
            $fields = $request->validate([
                'email' => 'required|email|unique:users,email',
                'phone_number' => 'required|numeric|min:10',
                'password' => 'required|string|confirmed|min:8'
            ]);
    
            $user = User::create([
                'email' => $fields['email'],
                'phone_number' => $fields['phone_number'],
                'password' => bcrypt($fields['password']),
                'verification_code' => Str::random(60)
            ]);

            $user->notify(new UserNotification($user));

            return response()->json(['success' => true, 'user'=>$user], 201);

        } catch (Exception $ex) {
            return response()->json(['success' => false, 'error'], 500);
        }
    }

    //
    public function login(Request $request){
        try {
            $fields = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:8'
            ]);

            if(!Auth::attempt($fields)){
                $error['message'] = 'Invalid Credentials';
                return response()->json(['success' => false, 'error' => $error], 401);
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

    //
    public function verifyAccount(Request $request){
        try {
            $verification_code = $request->query('cl');
            $user = User::where('verification_code', $verification_code)->first();
            if($user == null)
                return response()->json(['success' => false, 'error' =>  array('message' => 'Code not Found') ]);

            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->save();

            return response()->json(['success' => true, 'message' => 'Account Verified Successfully' ]);

        } catch (Exception $ex) {
            return response()->json(['success' => false, 'errors' => array('message' => $ex.getMessage()) ], 500);
        }
    }

    //
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request['email'])->first();
            if($user == null)
                return response()->json(['success' => false, 'error' =>  array('message' => 'User not Found') ]);
 
            $status = Password::sendResetLink(
                $request->only('email')
            );

            // return $status === Password::RESET_LINK_SENT
            //             ? back()->with(['status' => __($status)])
            //             : back()->withErrors(['email' => __($status)]);

            return response()->json(['success' => true, 'message' => 'A Code has been sent to your email. Use the code to reset your Password' ], 200);

        } catch (Exception $ex) {
            return response()->json(['success' => false, 'errors' => array('message' => $ex.getMessage()) ], 500);
        }
    }

    public function resetPassword(Request $request){
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);
         
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => bcrypt($password)
                    ])->setRememberToken(Str::random(60));
         
                    $user->save();
         
                    event(new PasswordReset($user));
                }
            );
         
            // return $status === Password::PASSWORD_RESET
            //             ? redirect()->route('login')->with('status', __($status))
            //             : back()->withErrors(['email' => [__($status)]]);

            return response()->json(['success' => true, 'message' => 'Password Reset successful' ], 200);

        } catch (Exception $ex) {
            return response()->json(['success' => false, 'errors' => array('message' => $ex.getMessage()) ], 500);
        }
    }

    //
    public function verifyAccounts(Request $request){
        try {
            // Verify Account
            $fields = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'other_name' => 'required|string',
                'phone_number' => 'required|string',
                'residential_address' => 'required|string',
                'state' => 'required|string',
                'lga' => 'required|string',
            ]);

        } catch (Exception $ex) {
            return response()->json(['success' => false, 'errors' => array('message' => $ex.getMessage()) ], 500);
        }
    }
}
