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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function user(){
        return response()->json(['message' => 'i m here.'], 200);

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
    

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $farmer = Farmer::where('email', $request->email)->first();

        if (!$farmer) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        $email = $farmer->email;

        $existing = DB::table('password_resets')->where('email', $email)->first();
        if ($existing) {
            return response()->json(['message' => 'Reset link already sent.'], 200);
        }

        $token = Str::random(60);
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => now()
        ]);

        $link = url("/api/verify-reset-token/{$token}");
        Mail::to($email)->send(new ResetPasswordMail(['url' => $link]));

        return response()->json(['message' => 'Reset link sent successfully.'], 200);
    }

    public function verifyResetToken($token)
    {
        $record = DB::table('password_resets')->where('token', $token)->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        return response()->json(['message' => 'Token is valid.', 'email' => $record->email], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_resets')->where([
            ['email', '=', $request->email],
            ['token', '=', $request->token],
        ])->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid token or email.'], 400);
        }

        $hashedPassword = Hash::make($request->password);

        $farmer = Farmer::where('email', $request->email)->first();

        if ($farmer) {
            $farmer->update(['password' => $hashedPassword]);
        } else {
            return response()->json(['message' => 'User not found.'], 404);
        }

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully.'], 200);
    }

    public function getProfile(Request $request)
    {
        // Check if the user is authenticated as Farmer
        $user = Auth::guard('api')->user(); // Retrieve authenticated user

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401); // If no user found, return 401
        }

        return response()->json([
            'data' => $user
        ]);
    }

    public function updateProfile(Request $request)
{
    $user = Auth::guard('api')->user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Custom validator to handle validation errors manually
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'email' => 'required|email|unique:farmers,email,' . $user->id,
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => 'Validation failed',
            'messages' => $validator->errors()
        ], 422);
    }

    $data = $request->only(['name', 'email']);

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '.' . $extension;
        $file->move(public_path('farmers/assets/images'), $filename);
        $data['image'] = 'farmers/assets/images/' . $filename;
    }

    $user->update($data);

    return response()->json(['message' => 'Profile updated successfully']);
}
    

}
