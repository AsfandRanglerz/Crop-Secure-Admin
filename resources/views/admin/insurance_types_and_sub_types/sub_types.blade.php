@extends('admin.layout.app')
@section('title', 'Insurance Sub-Types')
@section('content')



    <!-- Add Insurance Sub-Types Modal -->
    <div class="modal fade" id="InsuranceTypesModal" tabindex="-1" role="dialog" aria-labelledby="InsuranceTypesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="insuranceSubTypeForm" action="{{ route('insurance.sub.type.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="InsuranceTypesModalLabel">Create Insurance Sub-Type</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="incurance_type_id" value="{{ $InsuranceType->id }}">

                        <div class="row">
                            {{-- Crop --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Crop</label>
                                    <select name="name" class="form-control">
                                        <option value="">Select Crop</option>
                                        @foreach ($ensuredCrops as $crop)
                                            <option value="{{ $crop->name }}"
                                                {{ old('name') == $crop->name ? 'selected' : '' }}>
                                                {{ $crop->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- District --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="district_id">District</label>
                                    <select id="districtAdd" name="district_id" class="form-control">
                                        <option value="">Select District</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Tehsil --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tehsil_id">Tehsil</label>
                                    <select id="tehsilAdd" name="tehsil_id" class="form-control">
                                        <option value="">Select Tehsil</option>
                                    </select>
                                    @error('tehsil_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Current Yield --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_yield">Current Yield</label>
                                    <div class="input-group">
                                        <input type="number" name="current_yield" class="form-control"
                                            value="{{ old('current_yield') }}">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text font-weight-bold">%</span>
                                        </div>
                                    </div>
                                    @error('current_yield')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Year --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    {{-- <input type="text" name="year" class="form-control"
                                        value="{{ old('year', 2026) }}" readonly> --}}
                                        <input type="text" name="year" class="form-control"
                                        value="{{ old('year', now()->year) }}" readonly>
                                    @error('year')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div> <!-- end .row -->
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- Edit Modal --}}

    @foreach ($InsuranceSubTypes as $InsuranceSubType)
        <div class="modal fade" id="EditInsuranceTypesModal-{{ $InsuranceSubType->id }}" tabindex="-1" role="dialog"
            aria-labelledby="EditInsuranceTypesModalLabel" aria-hidden="true" data-id="{{ $InsuranceSubType->id }}"
            data-district-id="{{ $InsuranceSubType->district_id }}" data-tehsil-id="{{ $InsuranceSubType->tehsil_id }}">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Insurance Sub-Type</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <form class="editInsuranceSubTypeForm"
                        action="{{ route('insurance.sub.type.update', $InsuranceSubType->id) }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <input type="hidden" name="incurance_type_id" value="{{ $InsuranceType->id }}">
                            <div class="row">
                                <!-- Crop -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Crop</label>
                                        <select name="name" class="form-control form-select">
                                            <option value="">Select Crop</option>
                                            @foreach ($ensuredCrops as $crop)
                                                <option value="{{ $crop->name }}"
                                                    {{ old('name', $InsuranceSubType->name) == $crop->name ? 'selected' : '' }}>
                                                    {{ $crop->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- District -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="district_id">District</label>
                                        <select class="form-control form-select district-select" name="district_id"
                                            data-id="{{ $InsuranceSubType->id }}">
                                            <option value="">Select District</option>
                                            @foreach ($districts as $district)
                                                <option value="{{ $district->id }}"
                                                    {{ old('district_id', $InsuranceSubType->district_id) == $district->id ? 'selected' : '' }}>
                                                    {{ $district->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Tehsil -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tehsil_id">Tehsil</label>
                                        <select class="form-control form-select tehsil-select" name="tehsil_id"
                                            id="tehsilEdit-{{ $InsuranceSubType->id }}">
                                            <option value="">Select Tehsil</option>
                                            {{-- Options will be loaded via JS --}}
                                        </select>
                                    </div>
                                </div>

                                <!-- Current Yield -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="current_yield">Current yield</label>
                                        <div class="input-group">
                                            <input type="text" name="current_yield" class="form-control"
                                                value="{{ old('current_yield', $InsuranceSubType->current_yield) }}">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text font-weight-bold">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Year -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="year">Year</label>
                                        <input type="text" name="year" class="form-control"
                                            value="{{ old('year', $InsuranceSubType->year) }}" readonly>
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
                                        @foreach ($InsuranceSubTypes->pluck('name')->unique() as $name)
                                            <option value="{{ $name }}">{{ $name }}</option>
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
                                {{-- <select id="yieldFilter" class="form-control "style="width: 160px;">
                                    <option value="">Current Yield</option>
                                    @foreach ($InsuranceSubTypes->pluck('current_yield')->unique() as $yield)
                                        <option value="{{ $yield }}">{{ $yield }}</option>
                                    @endforeach
                                </select> --}}
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
                                            <th>Current Yield</th>
                                            <th>Year</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($InsuranceSubTypes as $InsuranceSubType)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="crop">{{ $InsuranceSubType->name }}</td>
                                                <td class="district">
                                                    {{ $InsuranceSubType->district->name ?? 'No district' }}</td>
                                                <td class="tehsil">{{ $InsuranceSubType->tehsil->name ?? 'No tehsil' }}
                                                </td>
                                                <td>{{ $InsuranceSubType->current_yield }}%</td>
                                                <td class="year">{{ $InsuranceSubType->year }}</td>
                                                {{-- <td>
                                                @if ($InsuranceSubType->status == 1)
                                                <div class="badge badge-success badge-shadow">Activated</div>
                                                @else
                                                    <div class="badge badge-danger badge-shadow">Deactivated</div>
                                                @endif
                                            </td> --}}
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
                                                        {{ route('insurance.sub.type.destroy', $InsuranceSubType->id) }}
                                                            "
                                                                method="POST"
                                                                style="display:inline-block; margin-left: 10px">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="incurance_type_id"
                                                                    value="{{ $InsuranceType->id }}">
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
            /** ========== ADD FORM HANDLING ========== */

            $('#districtAdd').change(function() {
                let districtId = $(this).val();
                let tehsilDropdown = $('#tehsilAdd');

                tehsilDropdown.html('<option value="">Loading...</option>');

                if (districtId) {
                    $.ajax({
                        url: `{{ route('get.tehsils', ':districtId') }}`.replace(':districtId',
                            districtId),
                        method: 'GET',
                        success: function(data) {
                            tehsilDropdown.empty().append(
                                '<option value="">Select Tehsil</option>');
                            data.forEach(function(tehsil) {
                                tehsilDropdown.append(
                                    `<option value="${tehsil.id}">${tehsil.name}</option>`
                                );
                            });
                        },
                        error: function(xhr) {
                            console.error('Error fetching tehsils:', xhr);
                            tehsilDropdown.html('<option value="">Failed to load</option>');
                        }
                    });
                } else {
                    tehsilDropdown.html('<option value="">Select Tehsil</option>');
                }
            });


            /** ========== EDIT FORM HANDLING ========== */
            function loadTehsilsForEdit(districtId, selectedTehsil = null) {
                $('#tehsilEdit').empty().append('<option value="">Select Tehsil</option>');

                if (districtId) {
                    $.ajax({
                        url: `{{ route('get.tehsils', ':districtId') }}`.replace(':districtId',
                            districtId),
                        method: 'GET',
                        success: function(data) {
                            data.forEach(function(tehsil) {
                                let isSelected = selectedTehsil == tehsil.id ? 'selected' : '';
                                $('#tehsilEdit').append(
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

            // Auto-load tehsils in edit form when the page loads
            let selectedDistrict = "{{ old('district_name', $InsuranceSubType->district_name ?? '') }}";
            let selectedTehsil = "{{ old('tehsil_id', $InsuranceSubType->tehsil_id ?? '') }}";

            if (selectedDistrict) {
                $('#districtEdit').val(selectedDistrict).trigger('change'); // Set district
                loadTehsilsForEdit(selectedDistrict, selectedTehsil); // Load tehsils and set selected one
            }

            // Update tehsils when changing district in edit form
            $('#districtEdit').change(function() {
                let districtId = $(this).val();
                loadTehsilsForEdit(districtId);
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
                    let year = row.querySelector("td:nth-child(6)").textContent.toLowerCase();

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
        // When modal opens, fetch Tehsils and populate
        $('.modal').on('shown.bs.modal', function() {
            let modal = $(this);
            let districtId = modal.data('district-id');
            let tehsilId = modal.data('tehsil-id');
            let modalId = modal.data('id');

            if (districtId) {
                loadTehsils(districtId, modalId, tehsilId);
            }
        });

        // On district change
        $('.district-select').on('change', function() {
            let districtId = $(this).val();
            let modalId = $(this).data('id');
            $('#tehsilEdit-' + modalId).html('<option value="">Loading...</option>');

            if (districtId) {
                loadTehsils(districtId, modalId);
            }
        });

        function loadTehsils(districtId, modalId, selectedTehsilId = null) {
            $.ajax({
                url: '{{ route('get.tehsils', ':districtId') }}'.replace(':districtId', districtId),
                method: 'GET',
                success: function(data) {
                    let tehsilDropdown = $('#tehsilEdit-' + modalId);
                    tehsilDropdown.empty().append('<option value="">Select Tehsil</option>');

                    data.forEach(function(tehsil) {
                        let selected = selectedTehsilId == tehsil.id ? 'selected' : '';
                        tehsilDropdown.append(
                            `<option value="${tehsil.id}" ${selected}>${tehsil.name}</option>`);
                    });
                },
                error: function() {
                    alert('Failed to load tehsils.');
                }
            });
        }
    </script>
    <script>
        @if ($errors->has('duplicate'))
            toastr.error("{{ $errors->first('duplicate') }}");
        @endif
    </script>

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
            $('.insuranceSubTypeForm').on('submit', function(e) {
                let isValid = true;
                const form = $(this);

                // Remove previous error messages
                form.find('.text-danger').remove();

                const fields = [{
                        name: 'name',
                        message: 'Crop is required.'
                    },
                    {
                        name: 'district_id',
                        message: 'District is required.'
                    },
                    {
                        name: 'tehsil_id',
                        message: 'Tehsil is required.'
                    },
                    {
                        name: 'current_yield',
                        message: 'Current Yield is required.'
                    }
                ];

                fields.forEach(field => {
                    const input = form.find(`[name="${field.name}"]`);

                    if (!input.val()) {
                        isValid = false;

                        // If input group, insert error after group
                        if (input.closest('.input-group').length) {
                            input.closest('.input-group').after(
                                `<span class="text-danger d-block">${field.message}</span>`);
                        } else {
                            input.after(`<span class="text-danger">${field.message}</span>`);
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault(); // Prevent form submission if any field is empty
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.editInsuranceSubTypeForm').on('submit', function(e) {
                let isValid = true;
                const form = $(this);

                // Clear previous error messages
                form.find('.text-danger').remove();

                const fields = [{
                        name: 'name',
                        label: 'Crop is required.'
                    },
                    {
                        name: 'district_id',
                        label: 'District is required.'
                    },
                    {
                        name: 'tehsil_id',
                        label: 'Tehsil is required.'
                    },
                    {
                        name: 'current_yield',
                        label: 'Current Yield is required.'
                    }
                ];

                fields.forEach(field => {
                    const input = form.find(`[name="${field.name}"]`);

                    if (!input.val()) {
                        isValid = false;

                        // If it's an input-group like current_yield with %, place error after input-group
                        if (input.closest('.input-group').length > 0) {
                            input.closest('.input-group').after(
                                `<span class="text-danger d-block">${field.label}</span>`);
                        } else {
                            input.after(`<span class="text-danger">${field.label}</span>`);
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault(); // Stop form from submitting if validation fails
                }
            });
        });
    </script>


@endsection
