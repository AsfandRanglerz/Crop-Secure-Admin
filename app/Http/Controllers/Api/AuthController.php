<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordMail;
use App\Mail\SendOtp;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function user(){
        return response()->json(['message' => 'i m here.'], 200);

    }

    public function register(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:farmers,email',
            'cnic' => [
                'required',
                'unique:farmers,cnic'
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // raw password for email
        $rawPassword = $request->password;

        $user = Farmer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cnic' => $request->cnic,
            'contact' => $request->phone,
            'dob' => $request->dob,
        ]);

        $mailData = [
            'name' => $user->name,
            'useremail' => $user->email,
            'password' => $rawPassword,
            'logo' => 'http://localhost/Crop-Secure-Admin/public/admin/assets/img/logo.png'
        ];


        // dd($mailData);
        Mail::to($user->email)->send(new WelcomeMail($mailData));

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Farmer registered successfully',
            'Farmer' => $user->makeHidden(['password']),
            'token' => $token,
        ]);
    }


    public function login(Request $request)
    {
        $email = $request->header('email') ?? $request->input('email');
        $password = $request->header('password') ?? $request->input('password');
    
        if (!$email || !$password) {
            return response()->json(['message' => 'Email or Password required'], 422);
        }
    
        $user = Farmer::where('email', $email)->first();
    
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        if ($user->status != 1) {
            return response()->json(['message' => 'Your account is deactivated'], 403);
        }
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Login Successful',
            'token' => $token,
            'user' => $user,
            'role' => 'farmer'
        ]);
    }
    

    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = Farmer::where('email', $request->email)
                        ->first();

            if (!$user) {
                return response()->json(['message' => 'Please enter a valid email address.'], 404);
            }

            $otp = random_int(1000, 9999);

            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => $otp,
                    'created_at' => now(),
                ]
            );

            // send email
            Mail::to($request->email)->send(new SendOtp($otp));

            return response()->json([
                'message' => 'OTP has been sent to your email successfully.',
                'email' => $request->email
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while sending the OTP.'], 500);
        }
    }


    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'otp' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $check = DB::table('password_resets')
                        ->where('email', $request->email)
                        ->where('token', $request->otp)
                        ->first();

            if ($check) {

                DB::table('password_resets')->where('email', $request->email)->delete();

                return response()->json([
                    'message' => 'OTP verified successfully.'
                ], 200);
            }

            return response()->json(['message' => 'Invalid OTP. Please try again.'], 401);

        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while verifying the OTP.'], 500);
        }
    }




    public function passwordReset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:8',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $user = Farmer::where('email', $request->email)->first();
    
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }
    
            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Your new password cannot be the same as the old one.'
                ], 400);
            }

            $user->password = Hash::make($request->password);
            $user->save();
    
            return response()->json([
                'message' => 'Password changed successfully.'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }
    

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while logging out.'], 500);
        }
    }

    // public function getProfile(Request $request)
    // {
    //     // Check if the user is authenticated as Farmer
    //     $user = Auth::guard('api')->user(); // Retrieve authenticated user

    //     if (!$user) {
    //         return response()->json(['message' => 'Unauthorized'], 401); // If no user found, return 401
    //     }

    //     return response()->json([
    //         'data' => $user
    //     ]);
    // }

    // public function updateProfile(Request $request)
    // {
    //     $user = Auth::guard('api')->user();

    //     if (!$user) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     // Custom validator to handle validation errors manually
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string',
    //         'email' => 'required|email|unique:farmers,email,' . $user->id,
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'error' => 'Validation failed',
    //             'messages' => $validator->errors()
    //         ], 422);
    //     }

    //     $data = $request->only(['name', 'email']);

    //     if ($request->hasFile('image')) {
    //         $file = $request->file('image');
    //         $extension = $file->getClientOriginalExtension();
    //         $filename = time() . '.' . $extension;
    //         $file->move(public_path('farmers/assets/images'), $filename);
    //         $data['image'] = 'farmers/assets/images/' . $filename;
    //     }

    //     $user->update($data);

    //     return response()->json(['message' => 'Profile updated successfully']);
    // }
    

}
