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

            // Handle Weather Index and Satellite Index (NDVI) together
            if (in_array($insuranceType->name, ['Weather Index', 'Satellite Index (NDVI)'])) {
                $request->validate([
                    'premium_price' => 'required|numeric',
                    'weather_ndvi_crops' => 'required|array',
                ]);

                $existing = CompanyInsuranceType::where([
                    ['insurance_company_id', '=', $request->insurance_company_id],
                    ['insurance_type_id', '=', $insuranceTypeId],
                ])->where(function ($query) use ($request) {
                    foreach ($request->weather_ndvi_crops as $crop) {
                        $query->orWhere('crop', 'like', "%{$crop}%");
                    }
                })->exists();

                if ($existing) {
                    return redirect()->route('company.insurance.types.index', ['id' => $request->insurance_company_id])
                        ->with(['error' => "{$insuranceType->name} for one or more crops already exists."]);
                }

                $cropList = implode(", ", array_map('trim', $request->weather_ndvi_crops));

                CompanyInsuranceType::create([
                    'insurance_company_id' => $request->insurance_company_id,
                    'insurance_type_id' => $insuranceTypeId,
                    'crop' => $cropList,
                    'district_name' => null,
                    'tehsil_id' => null,
                    'benchmark' => $insuranceType->name === 'Satellite Index (NDVI)' ? 0.4 : null,
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
                // Weather Index or Satellite Index (NDVI)
                if ($insuranceType->name === 'Weather Index' || $insuranceType->name === 'Satellite Index (NDVI)') {
                    $request->validate([
                        'premium_price' => 'required|numeric',
                        'crop' => 'required|array',
                    ]);

                    $cropList = implode(", ", array_map('trim', $request->crop));

                    $company->update([
                        'premium_price' => $request->premium_price,
                        'crop' => $cropList,
                        'district_name' => null,
                        'tehsil_id' => null,
                        'benchmark' => $insuranceType->name === 'Satellite Index (NDVI)' ? 0.4 : null,
                        'price_benchmark' => null,
                    ]);
                }

                // Other insurance types (area yield, etc.)
                else {
                    $request->validate([
                        'crop' => 'required|array',
                        'district_name' => 'required|array',
                        'tehsil_id' => 'required|array',
                        'benchmark' => 'required|array',
                        'price_benchmark' => 'required|array',
                    ]);

                    $crop = $request->input('crop')[0] ?? null;
                    $district = $request->input('district_name')[0] ?? null;
                    $tehsil = $request->input('tehsil_id')[0] ?? null;

                    $company->update([
                        'crop' => $crop,
                        'district_name' => $district,
                        'tehsil_id' => $tehsil,
                        'premium_price' => null,
                        'benchmark' => implode("\n", array_filter($request->benchmark[$id] ?? [])),
                        'price_benchmark' => implode("\n", array_filter($request->price_benchmark[$id] ?? [])),
                    ]);
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
