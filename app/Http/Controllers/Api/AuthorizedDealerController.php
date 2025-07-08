<?php

namespace App\Http\Controllers\Api;

use App\Models\Farmer;
use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use App\Mail\WelcomeDealerMail;
use App\Models\AuthorizedDealer;
use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthorizedDealerController extends Controller
{
    public function authorizeDealerRegister(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make(
            $request->all(),
            [
                'email' => [
                    'required',
                    'email',
                    'unique:authorized_dealers,email',
                    function ($attribute, $value, $fail) {
                        if (Farmer::where('email', $value)->exists()) {
                            $fail('The email has already been used by a farmer');
                        }
                    }
                ],
                'cnic' => [
                    'required',
                    'unique:authorized_dealers,cnic',
                    function ($attribute, $value, $fail) {
                        if (Farmer::where('cnic', $value)->exists()) {
                            $fail('The cnic has already been used by a farmer');
                        }
                    }
                ],
                'phone' => [
                    'required',
                    'unique:authorized_dealers,contact',
                    function ($attribute, $value, $fail) {
                        if (Farmer::where('contact', $value)->exists()) {
                            $fail('The phone number has already been used by a farmer');
                        }
                    }
                ],
            ],
            [
                'phone.required' => 'The phone number is required',
                'phone.unique' => 'The phone number has already been taken',
            ]
        );

        $errors = $validator->errors();

        if ($errors->has('cnic')) {
            return response()->json(['message' => $errors->first('cnic')], 422);
        } elseif ($errors->has('email')) {
            return response()->json(['message' => $errors->first('email')], 422);
        } elseif ($errors->has('phone')) {
            return response()->json(['message' => $errors->first('phone')], 422);
        }
        // raw password for email
        $rawPassword = $request->password;

        $district = District::find($request->district_id);
        if (!$district) {
            return response()->json(['message' => 'Invalid district ID provided.'], 422);
        }

        $user = AuthorizedDealer::create([
            'name' => $request->name,
            'father_name' => $request->father_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cnic' => $request->cnic,
            'contact' => $request->phone,
            'dob' => $request->dob,
            'district_id' => $district->id,
            'image' => 'public/admin/assets/images/avator.png'
        ]);


        $mailData = [
            'name' => $user->name,
            'useremail' => $user->email,
            'password' => $rawPassword,
            'admin_email' => 'admin@cropsecure.com',
            'admin_phone' => '+92-300-0000000',
            'logo' => 'https://ranglerzbeta.in/cropssecure/public/admin/assets/img/logo.png'
        ];


        // dd($mailData);
        Mail::to($user->email)->send(new WelcomeDealerMail($mailData));

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Authorized dealer registered successfully',
            'Authorized dealer' => $user->makeHidden(['password']),
            'token' => $token,
        ]);
    }
}
