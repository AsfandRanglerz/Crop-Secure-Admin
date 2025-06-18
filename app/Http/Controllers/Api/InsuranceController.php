<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InsuranceHistory;

class InsuranceController extends Controller
{

    public function store(Request $request)
    {
        // Retrieve the currently authenticated user
        $user = auth()->user();

        // Validate the incoming request data
        $validatedData = $request->validate([
            'crop' => 'required|string',
            'area_unit' => 'required|string',
            'area' => 'required|numeric',
            'insurance_type' => 'required|string',
            'district' => 'required|string',
            'tehsil' => 'required|string',
            'company' => 'required|string',
            'farmer_name' => 'required|string',
            'premium_price' => 'required|numeric',
            'sum_insured' => 'required|numeric',
            'payable_amount' => 'required|numeric',
            'land' => 'required',
            'benchmark' => 'required',
            'benchmark_price' => 'required'
        ]);

        // Get the current year and format it
        $currentYear = date('y'); // Get the last two digits of the current year

        // Find the last receipt number for the current year
        $lastReceipt = InsuranceHistory::whereYear('created_at', date('Y'))
            ->orderBy('receipt_number', 'desc')
            ->first();

        // Generate the next receipt number
        $nextReceiptNumber = $lastReceipt ? intval(substr($lastReceipt->receipt_number, -2)) + 1 : 1;

        // Prepare the complete receipt number
        $receiptNumber = sprintf('%s-%02d', $currentYear, $nextReceiptNumber); // Format to YY-MM

        // Create a new insurance history entry
        $insurance = InsuranceHistory::create(array_merge($validatedData, [
            'user_id' => $user->id, // Add the user_id to the insurance data 
            'receipt_number' => $receiptNumber // Add the newly generated receipt number
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Insurance history recorded successfully',
            'data' => $insurance,
        ], 201);
    }

    public function getInsurances(Request $request)
    {
        $user = auth()->user();

        // Check authentication
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Get pagination parameters
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $perPage;

        // Query with pagination
        $query = InsuranceHistory::where('user_id', $user->id);
        $total = $query->count(); // total records
        $insurances = $query->offset($offset)->limit($perPage)->get();

        // Return paginated response correctly
        // return response()->json($insurances, 200);

        return response()->json([
            'data' => $insurances,
        ], 200);
    }
}
