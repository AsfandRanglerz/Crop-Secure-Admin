<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Mail\SendOtp;
use App\Models\Farmer;
use App\Mail\WelcomeMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use App\Models\AuthorizedDealer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function user()
    {
        return response()->json(['message' => 'i m here.'], 200);
    }

    public function register(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make(
            $request->all(),
            [
                'email' => [
                    'required',
                    'email',
                    'unique:farmers,email',
                    function ($attribute, $value, $fail) {
                        if (AuthorizedDealer::where('email', $value)->exists()) {
                            $fail('The email has already been used by a authorized dealer');
                        }
                    }

                ],
                'cnic' => [
                    'required',
                    'unique:farmers,cnic',
                    function ($attribute, $value, $fail) {
                        if (AuthorizedDealer::where('cnic', $value)->exists()) {
                            $fail('The cnic has already been used by a authorized dealer');
                        }
                    }
                ],
                'phone' => [
                    'required',
                    'unique:farmers,contact',
                    function ($attribute, $value, $fail) {
                        if (AuthorizedDealer::where('contact', $value)->exists()) {
                            $fail('The phone number has already been used by a authorized dealer');
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

        $user = Farmer::create([
            'name' => $request->name,
            'fname' => $request->fname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cnic' => $request->cnic,
            'contact' => $request->phone,
            'dob' => $request->dob,
            'fcm_token' => $request->fcm_token
        ]);

        $mailData = [
            'name' => $user->name,
            'useremail' => $user->email,
            'password' => $rawPassword,
            'logo' => 'http://localhost/Crop-Secure-Admin/public/admin/assets/img/logo.png'
        ];


        // dd($mailData);
        // Mail::to($user->email)->send(new WelcomeMail($mailData));

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
        $loginInput = $request->header('email') ?? $request->input('email');
        $password = $request->header('password') ?? $request->input('password');

        $user = Farmer::where('email', $loginInput)
            ->orWhere('cnic', $loginInput)
            ->orWhere('contact', $loginInput)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Account not found. Please try again or register.'], 401);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Incorrect password'], 401);
        }

        if ($user->status != 1) {
            return response()->json(['message' => 'Your account is deactivated'], 403);
        }

        // ✅ Update FCM Token if provided
        if ($request->has('fcm_token')) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'fname' => $user->fname,
                'email' => $user->email,
                'contact' => $user->contact,
                'cnic' => $user->cnic,
                'dob' => $user->dob ? Carbon::parse($user->dob)->format('d/m/Y') : null,
                'status' => $user->status,
                'image' => $user->image,
            ],
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
                return response()->json(['message' => 'Account not found.Please try again.'], 404);
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

    public function getProfile(Request $request)
    {
        // Check if the user is authenticated as Farmer
        $user = Auth::guard('api')->user(); // Retrieve authenticated user

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $dob = Carbon::parse($user->dob)->format('Y-m-d');
        return response()->json([
            'data' => [
                'id'  => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->contact,
                'cnic' => $user->cnic,
                'image' => $user->image,
                'status' => $user->status,
                'dob' => $dob,
            ]
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->name = $request->name ?? $user->name;
        $user->fname = $request->fname ?? $user->fname;
        $user->dob = $request->dob ?? $user->dob;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('admin/assets/images/users'), $filename);
            $user->image = 'public/admin/assets/images/users/' . $filename;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'id' => $user->id,
            'name' => $user->name,
            'fname' => $user->fname,
            'email' => $user->email,
            'cnic' => $user->cnic,
            'phone' => $user->contact,
            'dob' => $user->dob ? Carbon::parse($user->dob)->format('d/m/Y') : null,
            'image' => $user->image,
        ], 200);
    }


    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->delete();

            return response()->json([
                'message' => 'Account deleted successfully.'
            ], 200);
        }

        return response()->json([
            'message' => 'User not authenticated.'
        ], 401);
    }
}
