<?php

namespace App\Http\Controllers\Api;

use App\Models\Contactus;
use App\Mail\ContactUsMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    public function getContact()
    {
        $user = Auth::user();
        $contact = Contactus::first();
        return response()->json([
            'email' => $contact->email,
            'phone' => $contact->phone,
        ]);
    }

    public function sendEmail(Request $request)
    {
        $user = Auth::user();
        // $request->validate([
        //     'subject' => 'required|string',
        //     'message' => 'required|string',
        // ]);

        $admin = Contactus::first();

       
        Mail::to($admin)->send(new ContactUsMail($user->email));
        return response()->json([
            // 'status' => true,
            'message' => 'Your message has been sent successfully.',
            // 'data' => $contact
        ],200);
    }
}
