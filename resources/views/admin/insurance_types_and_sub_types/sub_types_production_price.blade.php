@extends('admin.layout.app')
@section('title', 'Production Price Index')
@section('content')



    {{-- Add Insurance Sub-Types Modal --}}
    <div class="modal fade" id="InsuranceTypesModal" tabindex="-1" role="dialog" aria-labelledby="InsuranceTypesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="InsuranceTypesModalLabel">Create Production Price Index</h5>
                    {{-- ({{ $InsuranceType->name }}) --}}
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="productionPriceForm" action="{{ route('insurance.sub.type.productionPrice.store') }}"
                    method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="incurance_type_id" value="{{ $InsuranceType->id }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="crop_name_id">Crop</label>
                                    <select name="crop_name_id" class="form-control">
                                        <option value="">Select Crop</option>
                                        @foreach ($ensuredCrops as $crop)
                                            <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                                        @endforeach
                                    </select>

                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="district">District</label>
                                    <select name="crops[0][district_id]" class="form-control district-select">
                                        <option value="">Select District</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('crops.0.district_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tehsil -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tehsil">Tehsil</label>
                                    <select name="crops[0][tehsil_id]" class="form-control tehsil-select">
                                        <option value="">Select Tehsil</option>
                                    </select>
                                    @error('crops.0.tehsil_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cost_of_production">Cost of Production</label>
                                    <div class="input-group">
                                        <input type="number" name="crops[0][cost_of_production]" class="form-control"
                                            value="{{ old('cost_of_production') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text font-weight-bold"
                                                style="border: 1px solid #cbd2d8;">PKR</span>
                                        </div>
                                    </div>
                                    @error('cost_of_production')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="average_yield">Average yield</label>
                                    <div class="input-group">
                                        <input type="number" name="crops[0][average_yield]" class="form-control"
                                            value="{{ old('average_yield') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text font-weight-bold"
                                                style="border: 1px solid #cbd2d8;">%</span>
                                        </div>
                                    </div>
                                    @error('average_yield')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_yield">Historical Average Market Price</label>
                                    <div class="input-group">
                                        <input type="number" name="crops[0][historical_average_market_price]"
                                            class="form-control" value="{{ old('historical_average_market_price') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text font-weight-bold"
                                                style="border: 1px solid #cbd2d8;">PKR</span>
                                        </div>
                                    </div>
                                    @error('historical_average_market_price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_yield">Real-time Market Price (AMP)</label>
                                    <div class="input-group">
                                        <input type="number" name="crops[0][real_time_market_price]" class="form-control"
                                            value="{{ old('real_time_market_price') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text font-weight-bold"
                                                style="border: 1px solid #cbd2d8;">PKR</span>
                                        </div>
                                    </div>
                                    @error('real_time_market_price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_yield">Insured Yield</label>
                                    <div class="input-group">
                                        <input type="number" name="crops[0][ensured_yield]" class="form-control"
                                            value="{{ old('ensured_yield') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text font-weight-bold"
                                                style="border: 1px solid #cbd2d8;">%</span>
                                        </div>
                                    </div>
                                    @error('ensured_yield')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <input type="text" name="year" class="form-control"
                                        value="{{ old('year', now()->year) }}" readonly>
                                    {{-- <input type="text" name="year" class="form-control"
                                        value="{{ old('year', 2026) }}" readonly> --}}
                                    @error('year')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                        <div id="productionFieldsWrapper"></div>
                        <button type="button" id="addCropRow" class="btn btn-sm btn-info">Add More</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    {{-- Edit Insurance Sub-Types Modal --}}
    @foreach ($InsuranceSubTypes as $InsuranceSubType)
        <div class="modal fade" id="EditInsuranceTypesModal-{{ $InsuranceSubType->id }}" tabindex="-1" role="dialog"
            aria-labelledby="EditInsuranceTypesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="EditInsuranceTypesModalLabel">Edit Production Price Index</h5>
                        {{-- ({{ $InsuranceType->name }}) --}}
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form class="editProductionForm" data-id="{{ $InsuranceSubType->id }}"
                        action="{{ route('insurance.sub.type.productionPrice.update', $InsuranceSubType->id) }}"
                        method="POST">

                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <div class="row">
                                <input type="hidden" name="incurance_type_id" value="{{ $InsuranceType->id }}">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="crop_name_id">Crop</label>
                                        <select name="crop_name_id" class="form-control">
                                            <option value="">Select Crop</option>
                                            @foreach ($ensuredCrops as $crop)
                                                <option value="{{ $crop->id }}"
                                                    {{ old('crop_name_id', $InsuranceSubType->crop_name_id) == $crop->id ? 'selected' : '' }}>
                                                    {{ $crop->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('crop_name_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <!-- District Dropdown -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="district">District</label>
                                        <select name="crops[0][district_id]" class="form-control district-select">
                                            <option value="">Select District</option>
                                            @foreach ($districts as $district)
                                                <option value="{{ $district->id }}"
                                                    {{ old('crops.0.district_id', $InsuranceSubType->district_id) == $district->id ? 'selected' : '' }}>
                                                    {{ $district->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('crops.0.district_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Tehsil Dropdown -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tehsil">Tehsil</label>
                                        <select name="crops[0][tehsil_id]" class="form-control tehsil-select"
                                            data-selected="{{ old('crops.0.tehsil_id', $InsuranceSubType->tehsil_id) }}">
                                            <option value="">Select Tehsil</option>
                                            {{-- Options will be populated dynamically via JS --}}
                                        </select>
                                        @error('crops.0.tehsil_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cost_of_production">Cost of Production</label>
                                        <input type="text" name="cost_of_production" class="form-control"
                                            value="{{ old('cost_of_production', $InsuranceSubType->cost_of_production) }}">
                                        @error('cost_of_production')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="average_yield">Average Yield</label>
                                        <input type="text" name="average_yield" class="form-control"
                                            value="{{ old('average_yield', $InsuranceSubType->average_yield) }}">
                                        @error('average_yield')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="historical_average_market_price">Historical Average Market
                                            Price</label>
                                        <input type="text" name="historical_average_market_price" class="form-control"
                                            value="{{ old('historical_average_market_price', $InsuranceSubType->historical_average_market_price) }}">
                                        @error('historical_average_market_price')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="real_time_market_price">Real-time Market Price (AMP)</label>
                                        <input type="text" name="real_time_market_price" class="form-control"
                                            value="{{ old('real_time_market_price', $InsuranceSubType->real_time_market_price) }}">
                                        @error('real_time_market_price')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ensured_yield">Insured Yield (IY)</label>
                                        <input type="text" name="ensured_yield" class="form-control"
                                            value="{{ old('ensured_yield', $InsuranceSubType->ensured_yield) }}">
                                        @error('ensured_yield')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="year">Year</label>
                                        <input type="text" name="year" class="form-control"
                                            value="{{ old('year', $InsuranceSubType->year) }}" readonly>
                                        @error('year')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <a class="btn btn-primary mb-2" href="{{ route('insurance.type.index') }}">Back</a>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">{{ $InsuranceType->name }}</h4>

                                <div class="d-flex gap-5">
                                    <select id="cropFilter" class="form-control form-select w-auto rounded mr-2">
                                        <option value="">Crops</option>
                                        @foreach ($InsuranceSubTypes->unique('crop_name_id') as $subtype)
                                            <option value="{{ strtolower($subtype->crop->name ?? '') }}">
                                                {{ $subtype->crop->name ?? 'Unknown Crop' }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select id="districtFilter" class="form-control form-select w-auto rounded mr-2">
                                        <option value="">Districts</option>
                                        @foreach ($InsuranceSubTypes->pluck('district.name')->unique() as $district)
                                            <option value="{{ $district }}">{{ $district }}</option>
                                        @endforeach
                                    </select>

                                    <select id="tehsilFilter" class="form-control form-select w-auto rounded mr-2">
                                        <option value="">Tehsil</option>
                                        @foreach ($InsuranceSubTypes->pluck('tehsil.name')->unique() as $tehsil)
                                            <option value="{{ $tehsil }}">{{ $tehsil }}</option>
                                        @endforeach
                                    </select>

                                    <select id="yearFilter" class="form-control form-select w-auto rounded mr-2">
                                        <option value="">Year</option>
                                        @foreach ($InsuranceSubTypes->pluck('year')->unique() as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                @if (Auth::guard('admin')->check() ||
                                        $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Types' &&
                                                $permission['permissions']->contains('create')))
                                    <a class="btn btn-primary mb-3 text-white" href="#" data-toggle="modal"
                                        data-target="#InsuranceTypesModal">Create</a>
                                @endif

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Crop</th>
                                            <th>District</th>
                                            <th>Tehsil</th>
                                            <th>Cost of Production</th>
                                            <th>Average Yield</th>
                                            <th>Historical Average Market Price</th>
                                            <th>Real-time Market Price (AMP)</th>
                                            <th>Insured Yield (IY)</th>
                                            <th>Year</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($InsuranceSubTypes as $InsuranceSubType)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $InsuranceSubType->crop->name ?? 'N/A' }}</td>
                                                <td class="district">
                                                    {{ $InsuranceSubType->district->name ?? 'No district' }}</td>
                                                <td class="tehsil">{{ $InsuranceSubType->tehsil->name ?? 'No tehsil' }}
                                                </td>
                                                <td>{{ $InsuranceSubType->cost_of_production }} PKR</td>
                                                <td>{{ $InsuranceSubType->average_yield }}%</td>
                                                <td>{{ $InsuranceSubType->historical_average_market_price }} PKR</td>
                                                <td>{{ $InsuranceSubType->real_time_market_price }} PKR</td>
                                                <td>{{ $InsuranceSubType->ensured_yield }}%</td>
                                                <td class="year">{{ $InsuranceSubType->year }}</td>
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Types' &&
                                                                        $permission['permissions']->contains('edit')))
                                                            <a class="btn btn-primary text-white" href="#"
                                                                data-toggle="modal"
                                                                data-target="#EditInsuranceTypesModal-{{ $InsuranceSubType->id }}">Edit</a>
                                                        @endif

                                                        <!-- Delete Button -->
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Types' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form
                                                                action="
                                                        {{ route('insurance.sub.type.productionPrice.destroy', $InsuranceSubType->id) }}
                                                            "
                                                                method="POST"
                                                                style="display:inline-block; margin-left: 10px">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="incurance_type_id"
                                                                    value="{{ $InsuranceType->id }}">
                                                                <button type="submit"
                                                                    class="btn btn-danger btn-flat show_subType_confirm"
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
        $('.show_subType_confirm').click(function(event) {
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
        document.addEventListener("DOMContentLoaded", function() {
            // Get all filter dropdowns
            let cropFilter = document.getElementById("cropFilter");
            let districtFilter = document.getElementById("districtFilter");
            let tehsilFilter = document.getElementById("tehsilFilter");
            let yearFilter = document.getElementById("yearFilter");

            // Get all table rows
            let tableRows = document.querySelectorAll("#table_id_events tbody tr");

            function filterTable() {
                let cropValue = cropFilter.value.toLowerCase();
                let districtValue = districtFilter.value.toLowerCase();
                let tehsilValue = tehsilFilter.value.toLowerCase();
                let yearValue = yearFilter.value.toLowerCase();

                tableRows.forEach(row => {
                    let crop = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
                    let district = row.querySelector("td:nth-child(3)").textContent.toLowerCase();
                    let tehsil = row.querySelector("td:nth-child(4)").textContent.toLowerCase();
                    let year = row.querySelector("td:nth-child(10)").textContent.toLowerCase();

                    // Check if the row matches all filters
                    let matchesCrop = cropValue === "" || crop.includes(cropValue);
                    let matchesDistrict = districtValue === "" || district.includes(districtValue);
                    let matchesTehsil = tehsilValue === "" || tehsil.includes(tehsilValue);
                    let matchesYear = yearValue === "" || year.includes(yearValue);

                    // Show or hide the row based on matching filters
                    if (matchesCrop && matchesDistrict && matchesTehsil && matchesYear) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }

            // Add event listeners to all filters
            cropFilter.addEventListener("change", filterTable);
            districtFilter.addEventListener("change", filterTable);
            tehsilFilter.addEventListener("change", filterTable);
            yearFilter.addEventListener("change", filterTable);
        });
    </script>

    <script>
        let productionIndex = 1;

        $('#addCropRow').click(function() {
            const newFields = `
            <div class="crop-field-group row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>District</label>
                        <select class="form-control form-select district-select" name="crops[${productionIndex}][district_id]">
                            <option value="">Select District</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->id }}">{{ $district->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tehsil</label>
                        <select class="form-control form-select tehsil-select" name="crops[${productionIndex}][tehsil_id]">
                            <option value="">Select Tehsil</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cost_of_production">Cost of Production</label>
                       <div class="input-group">
                            <input type="number" name="crops[${productionIndex}][cost_of_production]" class="form-control" step="0.01">
                            <div class="input-group-append">
                                <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">PKR</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="average_yield">Average Yield</label>
                        <div class="input-group">
                            <input type="number" name="crops[${productionIndex}][average_yield]" class="form-control" step="0.01">
                            <div class="input-group-append">
                                <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="historical_average_market_price">Historical Average Market Price</label>
                       <div class="input-group">
                            <input type="number" name="crops[${productionIndex}][historical_average_market_price]" class="form-control" step="0.01">
                            <div class="input-group-append">
                                <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">PKR</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="real_time_market_price">Real-time Market Price (AMP)</label>
                        <div class="input-group">
                            <input type="number" name="crops[${productionIndex}][real_time_market_price]" class="form-control" step="0.01">
                            <div class="input-group-append">
                                <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">PKR</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ensured_yield">Insured Yield (IY)</label>
                        <div class="input-group">
                            <input type="number" name="crops[${productionIndex}][ensured_yield]" class="form-control" step="0.01">
                            <div class="input-group-append">
                                <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
            $('#productionFieldsWrapper').append(newFields);
            productionIndex++;
        });
    </script>

    <script>
        $('#productionPriceForm').on('submit', function(e) {
            let isValid = true;

            const fields = [{
                    name: "crop_name_id",
                    type: "select",
                    message: "Crop is required."
                },
                {
                    name: "crops[0][district_id]",
                    type: "select",
                    message: "District is required."
                },
                {
                    name: "crops[0][tehsil_id]",
                    type: "select",
                    message: "Tehsil is required."
                },
                {
                    name: "crops[0][cost_of_production]",
                    type: "input",
                    message: "Cost of Production is required."
                },
                {
                    name: "crops[0][average_yield]",
                    type: "input",
                    message: "Average Yield is required."
                },
                {
                    name: "crops[0][historical_average_market_price]",
                    type: "input",
                    message: "Historical Avg. Market Price is required."
                },
                {
                    name: "crops[0][real_time_market_price]",
                    type: "input",
                    message: "Real-Time Market Price is required."
                },
                {
                    name: "crops[0][ensured_yield]",
                    type: "input",
                    message: "Insured Yield is required."
                },
            ];

            // Clear previous errors
            $(".text-danger").remove();

            fields.forEach(field => {
                const selector = field.type === "select" ?
                    `select[name='${field.name}']` :
                    `input[name='${field.name}']`;

                const input = $(selector);

                if (!input.val()) {
                    isValid = false;

                    if (input.closest('.input-group').length) {
                        // For PKR/% fields
                        input.closest('.input-group').after(
                            `<span class="text-danger d-block">${field.message}</span>`
                        );
                    } else {
                        input.after(
                            `<span class="text-danger">${field.message}</span>`
                        );
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.editProductionForm').on('submit', function(e) {
                let isValid = true;
                const form = $(this);
                const fields = [{
                        name: "crop_name_id",
                        type: "select",
                        message: "Crop is required."
                    },
                    {
                        name: "crops[0][district_id]",
                        type: "select",
                        message: "District is required."
                    },
                    {
                        name: "crops[0][tehsil_id]",
                        type: "select",
                        message: "Tehsil is required."
                    },
                    {
                        name: "cost_of_production",
                        type: "input",
                        message: "Cost of Production is required."
                    },
                    {
                        name: "average_yield",
                        type: "input",
                        message: "Average Yield is required."
                    },
                    {
                        name: "historical_average_market_price",
                        type: "input",
                        message: "Historical Avg. Market Price is required."
                    },
                    {
                        name: "real_time_market_price",
                        type: "input",
                        message: "Real-Time Market Price is required."
                    },
                    {
                        name: "ensured_yield",
                        type: "input",
                        message: "Insured Yield is required."
                    },
                ];

                // Clear previous errors
                form.find(".text-danger").remove();

                fields.forEach(field => {
                    const selector = field.type === "select" ?
                        `select[name='${field.name}']` :
                        `input[name='${field.name}']`;

                    const input = form.find(selector);

                    if (!input.val()) {
                        isValid = false;

                        if (input.closest('.input-group').length) {
                            input.closest('.input-group').after(
                                `<span class="text-danger d-block">${field.message}</span>`
                            );
                        } else {
                            input.after(`<span class="text-danger">${field.message}</span>`);
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault(); // Prevent form submission if invalid
                }
            });
        });
    </script>
    <script>
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error("{{ $error }}");
            @endforeach
        @endif
    </script>



@endsection
