<?php

namespace App\Http\Controllers\Api;

use App\Models\Faq;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    public function getfaq(Request $request)
{
    // Get pagination parameters
    $page = (int) $request->input('page', 1);
    $perPage = (int) $request->input('limit', 10);
    $offset = ($page - 1) * $perPage;

    // Apply pagination using skip & take
    $faqs = Faq::select('id', 'question', 'answer')
        ->skip($offset)
        ->take($perPage)
        ->get();

    if ($faqs->isEmpty()) {
        return response()->json(['message' => 'No FAQs found'], 404);
    }

    return response()->json([
        'data' => $faqs,
    ], 200);
}

}
