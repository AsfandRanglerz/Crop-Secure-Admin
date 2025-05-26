<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tehsil;
use App\Models\District;
use Illuminate\Http\Request;
use App\Models\InsuranceType;
use App\Models\EnsuredCropName;
use App\Models\InsuranceCompany;
use App\Models\InsuranceSubType;
use App\Http\Controllers\Controller;
use App\Models\CompanyInsuranceType;
use Illuminate\Support\Facades\Auth;

class CompanyInsuranceTypeController extends Controller
{
    public function index($id)
    {
        $Company = InsuranceCompany::find($id);

        $CompanyInsurances = CompanyInsuranceType::where('insurance_company_id', $id)->with('insuranceType')->orderBy('status', 'desc')->latest()->get();
        // dd($CompanyInsurances);

        // for creating time types
        $savedInsuranceTypeIds = CompanyInsuranceType::where('insurance_company_id', $id)
            ->pluck('insurance_type_id')
            ->toArray();
        $Insurance_types = InsuranceType::where('status', 1)
            // ->whereNotIn('id', $savedInsuranceTypeIds)
            ->orderBy('name', 'asc')
            ->get();

        // dd($CompanyInsurances);
        $ensuredCrops = EnsuredCropName::all();  // Fetch all crops
        $districts = District::all();         // Fetch all districts
        $tehsils = Tehsil::all();

        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        return view('admin.company_insurance.type', compact('sideMenuPermissions', 'sideMenuName', 'CompanyInsurances', 'Company', 'Insurance_types', 'ensuredCrops', 'districts', 'tehsils'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'insurance_company_id' => 'required',
            'insurance_type_id' => 'required|array',
        ]);

        foreach ($request->insurance_type_id as $insuranceTypeId) {
            $insuranceType = InsuranceType::find($insuranceTypeId);

            if (!$insuranceType) continue;

            // Handle Weather Index separately
            if ($insuranceType->name === 'Weather Index') {
                $request->validate([
                    'premium_price' => 'required|numeric',
                ]);

                CompanyInsuranceType::create([
                    'insurance_company_id' => $request->insurance_company_id,
                    'insurance_type_id' => $insuranceTypeId,
                    'crop' => null,
                    'district_name' => null,
                    'tehsil_id' => null,
                    'benchmark' => null,
                    'price_benchmark' => null,
                    'premium_price' => $request->premium_price,
                ]);
            }

            // Handle Satellite Index (NDVI) separately
            elseif ($insuranceType->name === 'Satellite Index (NDVI)') {
                $request->validate([
                    'premium_price' => 'required|numeric',
                ]);

                CompanyInsuranceType::create([
                    'insurance_company_id' => $request->insurance_company_id,
                    'insurance_type_id' => $insuranceTypeId,
                    'crop' => null,
                    'district_name' => null,
                    'tehsil_id' => null,
                    'benchmark' => 0.4,
                    'price_benchmark' => null,
                    'premium_price' => $request->premium_price,
                ]);
            }

            // Handle other insurance types (Area Yield, Production Price, etc.)
            else {
                $request->validate([
                    'crop' => 'required|array',
                    'district_name' => 'required|array',
                    'tehsil_id' => 'required|array',
                    'benchmark' => 'required|array',
                    'price_benchmark' => 'required|array',
                ]);

                foreach ($request->crop as $index => $crop) {
                    $exists = CompanyInsuranceType::where([
                        ['insurance_company_id', '=', $request->insurance_company_id],
                        ['insurance_type_id', '=', $insuranceTypeId],
                        ['crop', '=', $crop],
                        ['district_name', '=', $request->district_name[$index] ?? null],
                        ['tehsil_id', '=', $request->tehsil_id[$index] ?? null],
                    ])->exists();

                    if ($exists) {
                        return redirect()->route('company.insurance.types.index', ['id' => $request->insurance_company_id])
                            ->with(['error' => "The Crop, District, and Tehsil already exist for this Insurance."]);
                    }

                    $benchmarkArray = $request->benchmark[$index] ?? [];
                    $priceBenchmarkArray = $request->price_benchmark[$index] ?? [];

                    $formattedBenchmarks = implode("\n", array_map('trim', $benchmarkArray));
                    $formattedPriceBenchmarks = implode("\n", array_map('trim', $priceBenchmarkArray));

                    CompanyInsuranceType::create([
                        'insurance_company_id' => $request->insurance_company_id,
                        'insurance_type_id' => $insuranceTypeId,
                        'crop' => $crop,
                        'district_name' => $request->district_name[$index] ?? null,
                        'tehsil_id' => $request->tehsil_id[$index] ?? null,
                        'benchmark' => $formattedBenchmarks,
                        'price_benchmark' => $formattedPriceBenchmarks,
                        'premium_price' => null,
                    ]);
                }
            }
        }

        return redirect()->route('company.insurance.types.index', ['id' => $request->insurance_company_id])
            ->with(['message' => 'Company Insurance Created Successfully']);
    }


    public function update(Request $request, $id)
    {
        try {
            $company = CompanyInsuranceType::findOrFail($id);
            $insuranceType = InsuranceType::find($company->insurance_type_id);

            if ($insuranceType) {
                // Weather Index update
                if ($insuranceType->name === 'Weather Index') {
                    $request->validate([
                        'premium_price' => 'required|numeric',
                    ]);

                    $company->update([
                        'premium_price' => $request->premium_price,
                        'crop' => null,
                        'district_name' => null,
                        'tehsil_id' => null,
                        'benchmark' => null,
                        'price_benchmark' => null,
                    ]);
                }

                // Satellite Index (NDVI) update
                elseif ($insuranceType->name === 'Satellite Index (NDVI)') {
                    $request->validate([
                        'premium_price' => 'required|numeric',
                    ]);

                    $company->update([
                        'premium_price' => $request->premium_price,
                        'crop' => null,
                        'district_name' => null,
                        'tehsil_id' => null,
                        'benchmark' => 0.4, // Fixed NDVI benchmark
                        'price_benchmark' => null,
                    ]);
                }

                // Other insurance types
                else {
                    $request->validate([
                        'crop' => 'required|array',
                        'district_name' => 'required|array',
                        'tehsil_id' => 'required|array',
                        'benchmark' => 'required|array',
                        'price_benchmark' => 'required|array',
                    ]);

                    $company->update([
                        'crop' => $request->crop[$id] ?? $company->crop,
                        'district_name' => $request->district_name[$id] ?? $company->district_name,
                        'tehsil_id' => $request->tehsil_id[$id] ?? $company->tehsil_id,
                    ]);

                    $company->benchmark = implode("\n", array_filter($request->benchmark[$id] ?? []));
                    $company->price_benchmark = implode("\n", array_filter($request->price_benchmark[$id] ?? []));
                    $company->premium_price = null;
                    $company->save();
                }
            }

            return redirect()
                ->route('company.insurance.types.index', ['id' => $company->insurance_company_id])
                ->with(['message' => 'Company Insurance Updated Successfully']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }



    public function destroy(Request $request, $id)
    {
        // dd($request);
        CompanyInsuranceType::destroy($id);
        return redirect()->route('company.insurance.types.index', ['id' => $request->incurance_company_id])->with(['message' => 'Company Insurance Deleted Successfully']);
    }
}
