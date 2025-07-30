@extends('admin.layout.app')
@section('title', 'Company Insurance Types')
@section('content')

    <style>
        .select2-container {
            display: block;
        }
    </style>

    {{-- Add Insurance Types Modal --}}
    <div class="modal fade" id="InsuranceTypesModal" tabindex="-1" role="dialog" aria-labelledby="InsuranceTypesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="InsuranceTypesModalLabel">Add Insurance Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="insuranceTypeForm" action="{{ route('company.insurance.types.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="insurance_company_id" value="{{ $Company->id }}">
                        <div class="row" id="type">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="insurance_type_id">Types</label>
                                    <select name="insurance_type_id[]" id="insurance_type_id" class="form-control">
                                        @foreach ($Insurance_types->whereIn('name', ['Area Yield Index', 'Production Price Index', 'Weather Index', 'Satellite Index (NDVI)']) as $Insurance_type)
                                            <option value="{{ $Insurance_type->id }}">{{ $Insurance_type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('insurance_type_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <!-- Crop Selection for Weather/NDVI -->
                        <div class="row" id="weatherNdviCropRow" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="weather_ndvi_crops">Select Crops</label>
                                    <select name="weather_ndvi_crops[]" id="weather_ndvi_crops_select2" class="form-control"
                                        multiple>
                                        @foreach ($ensuredCrops as $crop)
                                            <option value="{{ $crop->name }}">{{ $crop->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('weather_ndvi_crops')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="ndviBenchmarkRow" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ndvi_fixed_benchmark">NDVI Benchmark</label>
                                    <div class="input-group">
                                        <input type="number" name="ndvi_fixed_benchmark" id="ndvi_fixed_benchmark"
                                            class="form-control" value="0.4" readonly disabled>
                                        {{-- <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div> --}}
                                    </div>
                                    {{-- <small class="text-muted mt-2 d-block">Fixed benchmark value for NDVI</small> --}}
                                </div>
                            </div>
                        </div>

                        <div class="row" id="premiumPriceWrapper" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="premium_price">Premium Price</label>
                                    <div class="input-group">
                                        <input type="number" name="premium_price" id="premium_price" class="form-control">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="border: 1px solid #cbd2d8;">PKR</span>
                                        </div>
                                    </div>
                                    @error('premium_price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror

                                    <!-- The required sum insured note -->
                                    <small class="text-muted mt-2 d-block" id="sumInsuredNote" style="display: none;">
                                        The premium price value applied against 1 acre
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="benchmarkFieldsWrapper">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="crop">Crop</label>
                                        <select name="crop[]" class="form-control">
                                            <option value="" disabled selected>Select Crop</option>
                                            @foreach ($ensuredCrops as $crop)
                                                <option value="{{ $crop->name }}">{{ $crop->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="crop-error"></div> {{-- 💡 Placeholder for JS error --}}
                                        @error('crop.*')
                                            <div><span class="text-danger">{{ $message }}</span></div>
                                        @enderror
                                    </div>
                                </div>



                                <!-- District Dropdown -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="district_name">District</label>
                                        <!-- ADD FORM -->
                                        <select name="district_name[]" class="form-control form-select district-select">
                                            <option value="">Select District</option>
                                            @foreach ($districts as $district)
                                                <option value="{{ $district->id }}">{{ $district->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('district_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tehsil_id">Tehsil</label>
                                        <select name="tehsil_id[]" class="form-control form-select tehsil-select">
                                            <option value="">Select Tehsil</option>
                                        </select>
                                        @error('tehsil')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="benchmark">Benchmark</label>
                                        <div class="input-group">
                                            <input type="number" name="benchmark[0][]" class="form-control"
                                                value="{{ old('benchmark') }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text" style="border: 1px solid #cbd2d8;">%</span>
                                            </div>
                                        </div>
                                        @error('benchmark')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="price_benchmark">Price Benchmark</label>
                                        <div class="d-flex">
                                            <div class="input-group">
                                                <input type="number" name="price_benchmark[0][]" class="form-control"
                                                    value="{{ old('price_benchmark') }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"
                                                        style="border: 1px solid #cbd2d8;">PKR</span>
                                                </div>
                                            </div>
                                            @error('price_benchmark')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <span class="btn btn-success ml-2 addBenchmark" id="addBenchmark">
                                                <i class="fa fa-plus"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Container for dynamically added fields -->
                                <div id="benchmarksContainer" class="col-md-12"></div>
                                <div id="fieldsContainer"></div>

                                <div class="col-" style="margin-left: 20px;">
                                    <button type="button" class="btn btn-success" id="addMore">
                                        Add More <span class="ml-2 fa fa-plus"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Insurance Types Modal --}}
    @foreach ($CompanyInsurances as $InsuranceType)
        @php
            $existingBenchmarks = explode("\n", $InsuranceType->benchmark ?? '');
            $existingPriceBenchmarks = explode("\n", $InsuranceType->price_benchmark ?? '');
        @endphp

        <div class="modal fade" id="EditInsuranceTypesModal-{{ $InsuranceType->id }}" tabindex="-1" role="dialog"
            aria-labelledby="EditInsuranceTypesModalLabel-{{ $InsuranceType->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Insurance Type</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <form class="editInsuranceTypeForm"
                        action="{{ route('company.insurance.types.update', $InsuranceType->id) }}" method="POST">
                        @csrf
                        @method('POST')

                        <div class="modal-body">
                            <input type="hidden" name="incurance_company_id" value="{{ $Company->id }}">

                            <div class="form-group">
                                <label>Insurance Type</label>
                                <input type="text" class="form-control"
                                    value="{{ $InsuranceType->insuranceType->name }}" readonly>
                            </div>

                            @if ($InsuranceType->insuranceType->name === 'Weather Index')
                                {{-- Weather Index: Show only Premium Price --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group" id="editPremiumPriceWrapper-{{ $InsuranceType->id }}">
                                            <label for="premium_price">Premium Price</label>
                                            <div class="input-group">
                                                <input type="number" name="premium_price" class="form-control"
                                                    value="{{ $InsuranceType->premium_price }}"
                                                    placeholder="Enter Premium Price">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">PKR</span>
                                                </div>
                                            </div>
                                            <small class="text-muted mt-1 d-block">
                                                The premium price value applied against 1 acre
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Crop</label>
                                            <select name="crop[]" class="form-control" multiple>
                                                <option value="" disabled>Select Crop</option>
                                                @php
                                                    $selectedCrops = is_array($InsuranceType->crop)
                                                        ? $InsuranceType->crop
                                                        : explode(',', $InsuranceType->crop);
                                                    $selectedCrops = array_map('trim', $selectedCrops); // remove whitespace
                                                @endphp

                                                @foreach ($ensuredCrops as $crop)
                                                    <option value="{{ $crop->name }}"
                                                        {{ in_array($crop->name, $selectedCrops) ? 'selected' : '' }}>
                                                        {{ $crop->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($InsuranceType->insuranceType->name === 'Satellite Index (NDVI)')
                                {{-- NDVI: Show Fixed Benchmark and Premium Price --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group" id="editNdviBenchmarkRow-{{ $InsuranceType->id }}">
                                            <label>NDVI Benchmark</label>
                                            <div class="input-group">
                                                <input type="number" name="benchmark" class="form-control"
                                                    value="{{ $InsuranceType->ndvi_fixed_benchmark ?? 0.4 }}" readonly
                                                    disabled>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group" id="editPremiumPriceWrapper-{{ $InsuranceType->id }}">
                                            <label for="premium_price">Premium Price</label>
                                            <div class="input-group">
                                                <input type="number" name="premium_price" class="form-control"
                                                    value="{{ $InsuranceType->premium_price }}"
                                                    placeholder="Enter Premium Price">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">PKR</span>
                                                </div>
                                            </div>
                                            <small class="text-muted mt-1 d-block">
                                                The premium price value applied against 1 acre
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Crop</label>
                                            <select name="crop[]" class="form-control" multiple>
                                                <option value="" disabled>Select Crop</option>
                                                @php
                                                    $selectedCrops = is_array($InsuranceType->crop)
                                                        ? $InsuranceType->crop
                                                        : explode(',', $InsuranceType->crop);
                                                    $selectedCrops = array_map('trim', $selectedCrops); // remove whitespace
                                                @endphp

                                                @foreach ($ensuredCrops as $crop)
                                                    <option value="{{ $crop->name }}"
                                                        {{ in_array($crop->name, $selectedCrops) ? 'selected' : '' }}>
                                                        {{ $crop->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Crop</label>
                                            <select name="crop[]" class="form-control">
                                                <option value="" disabled>Select Crop</option>
                                                @foreach ($ensuredCrops as $crop)
                                                    <option value="{{ $crop->name }}"
                                                        {{ $InsuranceType->crop == $crop->name ? 'selected' : '' }}>
                                                        {{ $crop->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>District</label>
                                            <select name="district_name[]" class="form-control district-select">
                                                {{-- <option value="">Select District</option> --}}
                                                @foreach ($districts as $district)
                                                    <option value="{{ $district->id }}"
                                                        {{ $InsuranceType->district_name == $district->id ? 'selected' : '' }}>
                                                        {{ $district->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tehsil</label>
                                            <select name="tehsil_id[]" class="form-control tehsil-select"
                                                data-selected="{{ $InsuranceType->tehsil_id }}">
                                                {{-- <option value="">Select Tehsil</option> --}}
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Initial Benchmark & Price Benchmark --}}
                                <div class="benchmarkContainer" data-index="{{ $InsuranceType->id }}">
                                    @foreach ($existingBenchmarks as $index => $b)
                                        <div class="row mb-2 benchmark-group">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Benchmark</label>
                                                    <div class="input-group">
                                                        <input type="number"
                                                            name="benchmark[{{ $InsuranceType->id }}][]"
                                                            class="form-control" value="{{ trim($b) }}">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Price Benchmark</label>
                                                    <div class="d-flex">
                                                        <div class="input-group">
                                                            <input type="number"
                                                                name="price_benchmark[{{ $InsuranceType->id }}][]"
                                                                class="form-control"
                                                                value="{{ trim($existingPriceBenchmarks[$index] ?? '') }}">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">PKR</span>
                                                            </div>
                                                        </div>

                                                        @if ($index === count($existingBenchmarks) - 1)
                                                            <!-- ✅ ADD THIS LINE -->
                                                            <button type="button"
                                                                class="btn btn-success ml-2 addBenchmark"
                                                                data-insurance-id="{{ $InsuranceType->id }}">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>


                                <div id="benchmarksContainer-{{ $InsuranceType->id }}"></div>
                            @endif
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Pass existing data to JS --}}
        <script>
            var existingBenchmarks_{{ $InsuranceType->id }} = @json(array_slice($existingBenchmarks, 1));
            var existingPriceBenchmarks_{{ $InsuranceType->id }} = @json(array_slice($existingPriceBenchmarks, 1));
        </script>
    @endforeach


    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <a class="btn btn-primary mb-2" href="{{ route('insurance.company.index') }}">Back</a>
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>{{ $Company->name }} - (Insurance types)</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                @if (Auth::guard('admin')->check() ||
                                        $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Companies' &&
                                                $permission['permissions']->contains('create')))
                                    <a class="btn btn-primary mb-3 text-white" href="#" data-toggle="modal"
                                        data-target="#InsuranceTypesModal">Add Insurance Types</a>
                                @endif

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Crop</th>
                                            <th>District</th>
                                            <th>Tehsil</th>
                                            <th>Benchmark % - Price</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($CompanyInsurances as $InsuranceType)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ $InsuranceType->insuranceType->name }}
                                                    @if (
                                                        ($InsuranceType->insuranceType->name === 'Weather Index' && $InsuranceType->premium_price) ||
                                                            $InsuranceType->insuranceType->name === 'Satellite Index (NDVI)')
                                                        <div class="text-muted small">
                                                            Premium Price: Rs
                                                            {{ number_format($InsuranceType->premium_price) }}
                                                            @if ($InsuranceType->insuranceType->name === 'Satellite Index (NDVI)')
                                                                <br>
                                                                NDVI Benchmark:
                                                                {{ $InsuranceType->benchmark ?? '0.4' }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $InsuranceType->crop }}</td>
                                                <td class="district">{{ $InsuranceType->district->name ?? '' }}
                                                </td>
                                                <td class="tehsil">{{ $InsuranceType->tehsil->name ?? '' }}</td>
                                                <td>
                                                    @if (!empty($InsuranceType->benchmark) && !empty($InsuranceType->price_benchmark))
                                                        @php
                                                            $benchmarks = explode(
                                                                "\n",
                                                                trim($InsuranceType->benchmark),
                                                            );
                                                            $priceBenchmarks = explode(
                                                                "\n",
                                                                trim($InsuranceType->price_benchmark),
                                                            );
                                                        @endphp
                                                        <ul>
                                                            @foreach ($benchmarks as $index => $benchmark)
                                                                @if (!empty(trim($benchmark)) && !empty(trim($priceBenchmarks[$index] ?? '')))
                                                                    <li>{{ trim($benchmark) }}% -
                                                                        {{ trim($priceBenchmarks[$index]) }} PKR</li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                    @endif
                                                </td>

                                                <td>
                                                    <div class="d-flex gap-4">
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Companies' &&
                                                                        $permission['permissions']->contains('edit')))
                                                            <a class="btn btn-primary text-white" href="#"
                                                                data-toggle="modal"
                                                                data-target="#EditInsuranceTypesModal-{{ $InsuranceType->id }}">Edit</a>
                                                        @endif

                                                        <!-- Delete Button -->
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Companies' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form
                                                                action="
                                                        {{ route('company.insurance.types.destroy', $InsuranceType->id) }}
                                                            "
                                                                method="POST"
                                                                style="display:inline-block; margin-left: 10px">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="incurance_company_id"
                                                                    value="{{ $Company->id }}">
                                                                <button type="submit"
                                                                    class="btn btn-danger btn-flat show_confirm"
                                                                    data-toggle="tooltip">Delete</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#table_id_events').DataTable()
        })
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script type="text/javascript">
        $('.show_confirm').click(function(event) {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();
            swal({
                    title: `Are you sure you want to delete this record?`,
                    text: "If you delete this, it will be gone forever.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#weather_ndvi_crops_select2').select2({
                placeholder: "Select Crops",
                width: '100%'
            });
        });

        $(document).ready(function() {
            $('select[name="crop[]"]').select2({
                placeholder: 'Select Crop(s)',
                width: '100%'
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            /** ========== ADD FORM HANDLING ========== */
            $(document).on('change', '.district-select', function() {
                let $district = $(this);
                loadTehsils($district);
                // let districtId = $(this).val();
                // let $tehsilSelect = $(this).closest('.row').find('.tehsil-select');

                $tehsilSelect.empty().append('<option value="">Select Tehsil</option>');

                if (districtId) {
                    $.ajax({
                        url: `{{ route('get.tehsils', ':districtId') }}`.replace(':districtId',
                            districtId),
                        method: 'GET',
                        success: function(data) {
                            data.forEach(function(tehsil) {
                                $tehsilSelect.append(
                                    `<option value="${tehsil.id}">${tehsil.name}</option>`
                                );
                            });
                        },
                        error: function(xhr) {
                            console.error('Error fetching tehsils:', xhr);
                        }
                    });
                }
            });

            /** ========== EDIT FORM HANDLING ========== */
            function loadTehsils($districtSelect, selectedTehsil = null) {
                let districtId = $districtSelect.val();
                let $row = $districtSelect.closest('.row');
                let $tehsilSelect = $row.find('.tehsil-select');

                $tehsilSelect.empty().append('<option value="">Select Tehsil</option>');

                if (districtId) {
                    $.ajax({
                        url: `{{ route('get.tehsils', ':districtId') }}`.replace(':districtId',
                            districtId),
                        method: 'GET',
                        success: function(data) {
                            data.forEach(function(tehsil) {
                                let isSelected = selectedTehsil == tehsil.id ? 'selected' : '';
                                $tehsilSelect.append(
                                    `<option value="${tehsil.id}" ${isSelected}>${tehsil.name}</option>`
                                );
                            });
                        },
                        error: function(xhr) {
                            console.error('Error fetching tehsils:', xhr);
                        }
                    });
                }
            }

            // Handle district change for all district dropdowns
            $(document).on('change', '.district-select', function() {
                let $district = $(this);
                loadTehsils($district);
            });

            // On page load, load tehsils for all pre-filled district fields (edit case)
            $('.district-select').each(function() {
                let $district = $(this);
                let selectedTehsil = $district.data('selected-tehsil') || $district.closest('.row').find(
                    '.tehsil-select').data('selected');
                if ($district.val()) {
                    loadTehsils($district, selectedTehsil);
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            let benchmarkIndex = 0;

            $(document).on('click', '#addBenchmark', function() {
                benchmarkIndex++;

                let benchmarkField = `
            <div class="row align-items-end benchmark-field" data-index="${benchmarkIndex}">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="number" name="benchmark[0][]" class="form-control" required>
                            <div class="input-group-append">
                                <span class="input-group-text" style="border: 1px solid #cbd2d8;">%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="d-flex">
                            <div class="input-group">
                                <input type="number" name="price_benchmark[0][]" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text" style="border: 1px solid #cbd2d8;">PKR</span>
                                </div>
                            </div>
                            <span class="btn btn-danger ml-2 removeBenchmark">
                                <i class="fa fa-trash"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;

                $('#benchmarksContainer').append(benchmarkField);
            });

            // Remove Benchmark Field
            $(document).on('click', '.removeBenchmark', function() {
                $(this).closest('.benchmark-field').remove();
            });
        });

        $(document).ready(function() {
            let rowIndex = 0;

            $('#addMore').click(function() {
                rowIndex++; // Increase index for each new row

                const fieldHTML = `
            <div class="row align-items-end field-group mt-4" data-index="${rowIndex}">
               <div class="col-md-4">
                    <div class="form-group">
                        <select name="crop[${rowIndex}]" class="form-control">
                            <option value="" disabled selected>Select Crop</option>
                            @foreach ($ensuredCrops as $crop)
                            <option value="{{ $crop->name }}">{{ $crop->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <select name="district_name[${rowIndex}]" class="form-control district-select">
                            <option value="" disabled selected>Select District</option>
                            @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                         <select name="tehsil_id[${rowIndex}]" class="form-control tehsil-select">
                            <option value="" disabled selected>Select Tehsil</option>
                        </select>
                    </div>
                </div>

                <!-- Benchmark & Price Benchmark Fields -->
                <div class="col-md-12 benchmarkContainer" data-index="${rowIndex}">
                    <div class="row align-items-end benchmark-group">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="number" name="benchmark[${rowIndex}][]" class="form-control">
                                    <div class="input-group-append">
                                        <span class="input-group-text" style="border: 1px solid #cbd2d8;">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="d-flex">
                                    <div class="input-group">
                                        <input type="number" name="price_benchmark[${rowIndex}][]" class="form-control">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="border: 1px solid #cbd2d8;">PKR</span>
                                        </div>
                                    </div>
                                    <span class="btn btn-danger ml-2 removeField">
                                        <i class="fa fa-trash"></i>
                                    </span>
                                    <span class="btn btn-success ml-2 addBenchmark">
                                        <i class="fa fa-plus"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
                $('#fieldsContainer').append(fieldHTML);
            });

            // Append Additional Benchmark & Price Benchmark Fields
            $(document).on('click', '.addBenchmark', function() {
                let parentIndex = $(this).closest('.benchmarkContainer').data(
                    'index'); // Get the correct row index

                const extraBenchmarkHTML = `
            <div class="row align-items-end benchmark-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="number" name="benchmark[${parentIndex}][]" class="form-control">
                            <div class="input-group-append">
                                <span class="input-group-text" style="border: 1px solid #cbd2d8;">%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="d-flex">
                            <div class="input-group">
                                <input type="number" name="price_benchmark[${parentIndex}][]" class="form-control">
                                <div class="input-group-append">
                                    <span class="input-group-text" style="border: 1px solid #cbd2d8;">PKR</span>
                                </div>
                            </div>
                            <span class="btn btn-danger ml-2 removeBenchmark">
                                <i class="fa fa-trash"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;

                // Append extraBenchmarkHTML inside the closest `.benchmarkContainer`
                $(this).closest('.benchmarkContainer').append(extraBenchmarkHTML);
            });

            // Remove Field (Crop, District, Tehsil & First Benchmark Set)
            $(document).on('click', '.removeField', function() {
                $(this).closest('.field-group').remove();
            });

            // Remove Individual Benchmark Fields
            $(document).on('click', '.removeBenchmark', function() {
                $(this).closest('.benchmark-group').remove();
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            function toggleFieldsBasedOnType() {
                let selectedOptions = $('#insurance_type_id option:selected').map(function() {
                    return $(this).text().trim();
                }).get();

                const isWeatherIndex = selectedOptions.includes('Weather Index');
                const isNDVI = selectedOptions.includes('Satellite Index (NDVI)');

                // Always hide everything initially
                $('.modal-body .row').hide();
                $('.benchmarkFieldsWrapper').hide();
                $('#premiumPriceWrapper').hide();
                $('#ndviBenchmarkRow').hide();
                $('#sumInsuredNote').hide();
                $('#addMore').hide();
                $('#weatherNdviCropRow').hide();

                if (isWeatherIndex) {
                    $('#type').show();
                    $('#premiumPriceWrapper').show();
                    $('#sumInsuredNote').show();
                    $('#weatherNdviCropRow').show();
                } else if (isNDVI) {
                    $('#type').show();
                    $('#premiumPriceWrapper').show();
                    $('#ndviBenchmarkRow').show();
                    $('#sumInsuredNote').show();
                    $('#weatherNdviCropRow').show();
                } else {
                    // Show everything for Area Yield Index or Production Price Index
                    $('.modal-body .row').show();
                    $('.benchmarkFieldsWrapper').show();
                    $('#premiumPriceWrapper').hide();
                    $('#ndviBenchmarkRow').hide();
                    $('#sumInsuredNote').hide();
                    $('#addMore').show();
                    $('#weatherNdviCropRow').hide();

                }
            }

            // On change
            $('#insurance_type_id').on('change', toggleFieldsBasedOnType);

            // On load
            toggleFieldsBasedOnType();
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#insuranceTypeForm').on('submit', function(e) {
                let isValid = true;
                const form = $(this);

                // Remove any previous error messages
                form.find('.text-danger').remove();

                const insuranceTypeText = $('#insurance_type_id option:selected').text().trim();

                const isWeatherOrNDVI = insuranceTypeText === 'Weather Index' || insuranceTypeText ===
                    'Satellite Index (NDVI)';

                // Validate Weather/NDVI crops
                if (isWeatherOrNDVI) {
                    const selectedWeatherCrops = $('#weather_ndvi_crops_select2').val();
                    if (!selectedWeatherCrops || selectedWeatherCrops.length === 0) {
                        isValid = false;
                        $('#weather_ndvi_crops_select2').after(
                            `<span class="text-danger d-block mt-1">Please select at least one crop.</span>`
                            );
                    }

                    const premium = $('#premium_price').val();
                    if (!premium || parseFloat(premium) <= 0) {
                        isValid = false;
                        $('#premium_price').closest('.input-group').after(
                            `<span class="text-danger d-block mt-1">Premium price is required.</span>`);
                    }
                } else {
                    // Validate Standard crop[]
                    form.find('select[name="crop[]"]').each(function() {
                        const cropErrorContainer = $(this).closest('.form-group').find(
                            '.crop-error');
                        cropErrorContainer.html(''); // clear previous error

                        if (!$(this).val()) {
                            isValid = false;
                            cropErrorContainer.html(
                                `<span class="text-danger d-block mt-1">Please select a crop.</span>`
                                );
                        }
                    });


                    // Validate District
                    form.find('select[name="district_name[]"]').each(function() {
                        if (!$(this).val()) {
                            isValid = false;
                            $(this).after(
                                `<span class="text-danger d-block mt-1">Please select a district.</span>`
                                );
                        }
                    });

                    // Validate Tehsil
                    form.find('select[name="tehsil_id[]"]').each(function() {
                        if (!$(this).val()) {
                            isValid = false;
                            $(this).after(
                                `<span class="text-danger d-block mt-1">Please select a tehsil.</span>`
                                );
                        }
                    });

                    // Validate at least one benchmark + price benchmark
                    // form.find('input[name^="benchmark"]').each(function () {
                    //     if (!$(this).val()) {
                    //         isValid = false;
                    //         $(this).closest('.input-group').after(`<span class="text-danger d-block mt-1">Benchmark is required.</span>`);
                    //     }
                    // });

                    // form.find('input[name^="price_benchmark"]').each(function () {
                    //     if (!$(this).val()) {
                    //         isValid = false;
                    //         $(this).closest('.input-group').after(`<span class="text-danger d-block mt-1">Price benchmark is required.</span>`);
                    //     }
                    // });
                }

                if (!isValid) e.preventDefault();
            });
        });
    </script>

    <script>
       $(document).on('submit', '.editInsuranceTypeForm', function (e) {
    const form = $(this);
    let isValid = true;

    // Clear old error messages
    form.find('.text-danger').remove();

    const insuranceTypeName = $.trim(form.find('input[readonly]').val());

    // Premium Price required only for Weather Index or Satellite Index (NDVI)
    if (insuranceTypeName === 'Weather Index' || insuranceTypeName === 'Satellite Index (NDVI)') {
        const premiumPriceInput = form.find('input[name="premium_price"]');
        if (!premiumPriceInput.val()) {
            isValid = false;
            premiumPriceInput
                .closest('.form-group')
                .append('<span class="text-danger d-block mt-1">Please enter premium price.</span>');
        }
    }

    // Validate crop[] (either single or multiple select)
    form.find('select[name="crop[]"]').each(function () {
        const cropField = $(this);
        if (!cropField.val() || (Array.isArray(cropField.val()) && cropField.val().length === 0)) {
            isValid = false;
            cropField
                .closest('.form-group')
                .append('<span class="text-danger d-block mt-1">Please select at least one crop.</span>');
        }
    });

    // Only apply district/tehsil check for non-NDVI/weather
    if (insuranceTypeName !== 'Weather Index' && insuranceTypeName !== 'Satellite Index (NDVI)') {
        // District check
        form.find('select[name="district_name[]"]').each(function () {
            const districtField = $(this);
            if (!districtField.val()) {
                isValid = false;
                districtField
                    .closest('.form-group')
                    .append('<span class="text-danger d-block mt-1">Please select a district.</span>');
            }
        });

        // Tehsil check
        form.find('select[name="tehsil_id[]"]').each(function () {
            const tehsilField = $(this);
            if (!tehsilField.val()) {
                isValid = false;
                tehsilField
                    .closest('.form-group')
                    .append('<span class="text-danger d-block mt-1">Please select a tehsil.</span>');
            }
        });
    }

    if (!isValid) {
        e.preventDefault();
    }
});

    </script>



@endsection
