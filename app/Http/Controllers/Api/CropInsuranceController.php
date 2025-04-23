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
            'crops' => EnsuredCropName::pluck('name')->toArray(),
            
            
            // 'area_units' => AreaUnit::select('id', 'unit_name')->get(),
            
        ]);
    }

    public function getinsurancetype()
    {
        $user = Auth::user();
        return response()->json([
            
            'insurance_types' => InsuranceType::pluck('name')->toArray(),
            
            // 'area_units' => AreaUnit::select('id', 'unit_name')->get(),
            
        ]);
    }

    // Get companies based on selected insurance type
    public function getCompaniesByInsuranceType($insuranceTypeId)
    {
        $companies = InsuranceCompany::whereHas('insuranceTypes', function ($q) use ($insuranceTypeId) {
            $q->where('insurance_type_id', $insuranceTypeId);
        })->select('id', 'name')->get();

       
        return response()->json(['message' => 'Companies retrieved successfully', 'data' => $companies], 200);
    }

    // Get benchmarks based on selected insurance type
    public function getBenchmarksByInsuranceType($insuranceTypeId)
    {
        $benchmarks = CompanyInsuranceType::where('insurance_type_id', $insuranceTypeId)
            ->with('insuranceCompany:id,name')
            ->get(['id', 'insurance_company_id', 'benchmark', 'price']);

    
        return response()->json(['message' => 'Benchmarks retrieved successfully', 'data' => $benchmarks], 200);
    }

    // Store insurance request
    public function store(Request $request)
    {
        // $request->validate([
        //     'crop_id' => 'required|exists:ensured_crop_names,id',
        //     'area_unit_id' => 'required|exists:area_units,id',
        //     'area' => 'required|numeric|min:0.1',
        //     'insurance_type_id' => 'required|exists:insurance_types,id',
        //     'insurance_company_id' => 'required|exists:insurance_companies,id',
        //     'benchmark_id' => 'required|exists:company_insurance_types,id',
        //     'premium_price' => 'required|numeric|min:0',
        //     'sum_insured' => 'required|numeric|min:0',
        // ]);

        $insurance = CropInsurance::create([
            // 'user_id' => Auth::id(),
            'crop' => $request->crop,
            'area_unit' => $request->area_unit,
            'area' => $request->area,
            'insurance_type' => $request->insurance_type,
            'company' => $request->company,
            'benchmark' => $request->benchmark,
            // 'premium_price' => $request->premium_price,
            // 'sum_insured' => $request->sum_insured,
        ]);

        return response()->json(['message' => 'Crop insurance submitted successfully', 'data' => $insurance], 200);
    }
}
