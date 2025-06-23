<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\CropInsurance;
use App\Models\InsuranceType;
use App\Models\EnsuredCropName;
use App\Models\InsuranceCompany;
use App\Http\Controllers\Controller;
use App\Models\CompanyInsuranceType;
use Illuminate\Support\Facades\Auth;

class CropInsuranceController extends Controller
{
    // Get dropdown data
    public function getFormOptions()
    {
        $user = Auth::user();
        return response()->json([
            'crops' => EnsuredCropName::select('id','name', 'sum_insured_value', 'insurance_start_time', 'insurance_end_time')->get(),
        ]);
    }

    // public function getinsurancetype()
    // {
    //     $user = Auth::user();
    //     return response()->json([

    //         // 'insurance_types' => InsuranceType::pluck('name')->toArray(),
    //         'insurance_types' => InsuranceType::select('id', 'name')->get(),
    //         // 'area_units' => AreaUnit::select('id', 'unit_name')->get(),

    //     ]);
    // }

    public function getinsurancetype(Request $request)
    {
        $cropName = $request->input('crop');

        if (!$cropName) {
            return response()->json(['error' => 'crop is required'], 400);
        }

        // Get unique insurance_type_ids for the crop
        $companyTypes = CompanyInsuranceType::where('crop', $cropName)
            ->with('insuranceType:id,name') // eager load insurance type
            ->get();

        // Fetch insurance type names
        $result = $companyTypes->map(function ($item) {
            $insuranceTypeName = $item->insuranceType->name ?? null;

            return [
                'id' => $item->insurance_type_id,
                'name' => $insuranceTypeName,
                // 'premium_price' => in_array($insuranceTypeName, ['Satellite Index (NDVI)', 'Weather Index']) ? $item->premium_price : null,
            ];
        })->unique('id')->values(); // remove duplicates by insurance_type_id

        return response()->json([
            'insurance_types' => $result
        ], 200);
    }

    // Get companies based on selected insurance type
    public function getCompaniesByInsuranceType($insuranceTypeId)
    {
        $companies = CompanyInsuranceType::where('insurance_type_id', $insuranceTypeId)
            ->with(['insuranceCompany:id,name', 'tehsil:id,name', 'district:id,name',  'insuranceType:id,name']) // eager load only required fields
            ->get()
            ->map(function ($item) {
                $insuranceTypeName = $item->insuranceType->name ?? null;

                $benchmarks = preg_split('/\r\n|\r|\n/', $item->benchmark);
                $prices = preg_split('/\r\n|\r|\n/', $item->price_benchmark);

                // Clean up extra whitespace and match lengths
                $benchmarks = array_map('trim', $benchmarks);
                $prices = array_map('trim', $prices);

                $combined = [];
                foreach ($benchmarks as $index => $value) {
                    $combined[] = [
                        'benchmark' => $value,
                        'price_benchmark' => $prices[$index] ?? null,
                    ];
                }

                return [
                    'company_name' => $item->insuranceCompany->name,
                    'tehsil_name' => $item->tehsil->name ?? null,
                    'district_name' => $item->district->name ?? null,
                    'premium_price' => in_array($insuranceTypeName, ['Satellite Index (NDVI)', 'Weather Index'])
                        ? $item->premium_price
                        : null,
                    'benchmark_data' => $combined,
                ];
            });


        return response()->json(['message' => 'Companies retrieved successfully', 'data' => $companies], 200);
    }

    // Get benchmarks based on selected insurance type
    public function getBenchmarksByInsuranceType($insuranceTypeId)
    {
        $benchmarks = CompanyInsuranceType::where('insurance_type_id', $insuranceTypeId)
            // ->with('insuranceCompany:id,name')
            ->get(['id', 'insurance_type_id', 'benchmark', 'price_benchmark'])
            ->map(function ($bench) {

                $bench->benchmark = preg_split('/\r\n|\r|\n/', $bench->benchmark);
                // Clean extra spaces from each item
                $bench->benchmark = array_filter(array_map('trim', $bench->benchmark));

                return $bench;

                $bench->price_benchmark = preg_split('/\r\n|\r|\n/', $bench->price_benchmark);
                // Clean extra spaces from each item
                $bench->price_benchmark = array_filter(array_map('trim', $bench->price_benchmark));

                return $bench;
            })
            ->map(function ($pricebench) {


                $pricebench->price_benchmark = preg_split('/\r\n|\r|\n/', $pricebench->price_benchmark);
                // Clean extra spaces from each item
                $pricebench->price_benchmark = array_filter(array_map('trim', $pricebench->price_benchmark));

                return $pricebench;
            });


        return response()->json(['message' => 'Benchmarks retrieved successfully', 'data' => $benchmarks], 200);
    }

    // Store insurance request
    public function store(Request $request)
    {
        $user = Auth::user();

        $benchmarkData = CompanyInsuranceType::where('insurance_type_id', $request->insurance_type)
            ->where('insurance_company_id', $request->company)
            ->first();

        if (!$benchmarkData) {
            return response()->json(['message' => 'Benchmark not found.'], 404);
        }

        // Explode values by newline and trim whitespace
        $benchmarks = array_map('trim', explode("\n", $benchmarkData->benchmark));
        $prices = array_map('trim', explode("\n", $benchmarkData->price_benchmark));

        // \Log::info('Parsed Benchmarks', ['benchmarks' => $benchmarks]);
        // \Log::info('Parsed Prices', ['prices' => $prices]);

        $benchmark = trim((string) $request->benchmark);
        $index = array_search($benchmark, $benchmarks);

        if ($index === false || !isset($prices[$index])) {
            return response()->json(['message' => 'Benchmark price not found.'], 404);
        }

        $premiumPrice = $prices[$index] * $request->area;

        $crop = EnsuredCropName::where('name', $request->crop)->first();

        if (!$crop) {
            return response()->json(['message' => 'Crop not found.'], 404);
        }

        // Multiply by area
        $sumInsured = $crop->sum_insured_value * $request->area;

        $insurance = CropInsurance::create([
            // 'user_id' => Auth::id(),
            'crop' => $request->crop,
            'area_unit' => $request->area_unit,
            'area' => $request->area,
            'insurance_type' => $request->insurance_type,
            'company' => $request->company,
            'benchmark' => $request->benchmark,
            'premium_price' => $premiumPrice,
            'sum_insured' => $sumInsured,
        ]);

        return response()->json(['message' => 'Crop insurance submitted successfully', 'data' => $insurance], 200);
    }

    public function claim()
    {
        $user = Auth::user();

        $insurances = CropInsurance::with(['companys', 'insuranceType', 'insuranceSubType'])
            ->get()
            ->map(function ($insurance) {
                $sumInsured = $insurance->sum_insured;
                $benchmark = $insurance->benchmark; // e.g., 100, 90, 60
                $currentYield = $insurance->insuranceSubType->current_yield ?? null;

                $refundAmount = 0;

                if ($currentYield !== null && $benchmark > $currentYield) {
                    $yieldLossPercentage = $benchmark - $currentYield;
                    $refundAmount = ($yieldLossPercentage / 100) * $sumInsured;
                }

                return [
                    'crop' => $insurance->crop,
                    'company' => $insurance->companys->name ?? 'N/A',
                    'insurance_type' => $insurance->insuranceType->name ?? 'N/A',
                    'premium_price' => $insurance->premium_price,
                    'sum_insured' => $sumInsured,
                    'benchmark' => $benchmark,
                    'current_yield' => $currentYield ?? 'N/A',
                    'refund_amount' => $refundAmount,
                ];
            });

        return response()->json(['data' => $insurances], 200);
    }

    public function getclaim()
    {
        $user = Auth::user();

        $insurances = CropInsurance::with(['companys', 'insuranceType', 'insuranceSubType'])
            ->get()
            ->map(function ($insurance) {
                $sumInsured = $insurance->sum_insured;
                $benchmark = $insurance->benchmark; // e.g., 100, 90, 60
                $currentYield = $insurance->insuranceSubType->current_yield ?? null;

                $refundAmount = 0;

                if ($currentYield !== null && $benchmark > $currentYield) {
                    $yieldLossPercentage = $benchmark - $currentYield;
                    $refundAmount = ($yieldLossPercentage / 100) * $sumInsured;
                }

                return [
                    'crop' => $insurance->crop,
                    // 'company' => $insurance->companys->name ?? 'N/A',
                    // 'insurance_type' => $insurance->insuranceType->name ?? 'N/A',
                    // 'premium_price' => $insurance->premium_price,
                    // 'sum_insured' => $sumInsured,
                    // 'benchmark' => $benchmark,
                    // 'current_yield' => $currentYield ?? 'N/A',
                    'refund_amount' => $refundAmount,
                ];
            });

        return response()->json(['data' => $insurances], 200);
    }

    public function postclaim(Request $request)
    {
        $user = Auth::user();
        $claim = CropInsurance::create([
            'crop' => $request->crop,
            'area_unit' => $request->area_unit,
            'area' => $request->area,
            'insurance_type' => $request->insurance_type,
            'company' => $request->company,
            'benchmark' => $request->benchmark,
            'premium_price' => $request->premium_price,
            'sum_insured' => $request->sum_insured,
        ]);
        return response()->json(['message' => 'Claim submitted successfully', 'data' => $claim], 200);
    }
}
