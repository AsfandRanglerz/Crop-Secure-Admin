<?php

namespace App\Http\Controllers\Api;

use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use App\Mail\WelcomeDealerMail;
use App\Models\AuthorizedDealer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthorizedDealerController extends Controller
{
    public function authorizeDealerRegister(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:authorized_dealers,email',
            'cnic' => [
                'required',
                'unique:authorized_dealers,cnic'
            ],
            'phone' => [
                'required',
                'unique:authorized_dealers,contact'
            ],
        ],
    [
        'phone.required' => 'The phone number is required',
        'phone.unique' => 'The phone number has already been taken',
    ]
    );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // raw password for email
        $rawPassword = $request->password;

        $user = AuthorizedDealer::create([
            'name' => $request->name,
            'father_name' => $request->father_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cnic' => $request->cnic,
            'contact' => $request->phone,
            'dob' => $request->dob,
            'district' => $request->district,

        ]);

        $mailData = [
            'name' => $user->name,
            'useremail' => $user->email,
            'password' => $rawPassword,
            'logo' => 'https://ranglerzbeta.in/lqappbackend/public/admin/assets/img/logo.png'
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
