<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\UserProfile;

class UserProfileController extends Controller
{
    //
    public function updateorcreateprofile(Request $request){
        try {
            // Verify Account
            $fields = $request->validate([
                'first_name' => 'nullable|string',
                'last_name' => 'nullable|string',
                'other_name' => 'nullable|string',
                'phone_number' => 'nullable|numeric|min:10',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'residential_address' => 'nullable|string',
                'state' => 'nullable|string',
                'lga' => 'nullable|string'
            ]);

            $fields['user_id'] = $request->user()->id;

            DB::BeginTransaction();

            if($request->hasFile('profile_picture')){
                // store image
                $file = $request->file('profile_picture')->store('profile');
                $fields['profile_picture'] = $file;
            }

            //
            $userprofile = UserProfile::create($fields);

            DB::commit();

            return response()->json(['success' => true, 'userprofile' => $userprofile]);

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => array('message' => $ex.getMessage()) ], 500);
        }
    }
}
