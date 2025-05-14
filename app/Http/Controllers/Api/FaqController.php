<?php

namespace App\Http\Controllers\Api;

use App\Models\Faq;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    public function getfaq()
{
    $faqs = Faq::select('id', 'question', 'answer')->get();

    if ($faqs->isEmpty()) {
        return response()->json(['message' => 'No FAQs found'], 404);
    }

    return response()->json([
        'message' => 'FAQs retrieved successfully',
        'data' => $faqs
    ], 200);
}
}
