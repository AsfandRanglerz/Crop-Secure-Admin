@extends('admin.layout.app')
@section('title', 'Satellite Index (NDVI)')
@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    {{-- Add Insurance Sub-Types Modal --}}
    <div class="modal fade" id="InsuranceTypesModal" tabindex="-1" role="dialog" aria-labelledby="InsuranceTypesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Satellite Index (NDVI)</h5>
                    <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close"><span>&times;</span></button>
                </div>
                <form id="ndviForm" action="{{ route('insurance.sub.type.satelliteNDVI.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="incurance_type_id" value="{{ $InsuranceType->id }}">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="date">Date</label>
                                <input type="date" class="form-control" name="date" id="ndvi_date">
                                <div class="text-danger date-error mt-1"></div>
                            </div>
                        </div>
                        <!-- Select Area -->
                        <div class="row">
                            <div class="mt-3 col-md-6">
                                <label for="area" class="d-block">Select Area</label>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" id="select_all_areas">
                                    <label class="form-check-label" for="select_all_areas">Select All</label>
                                </div>
                                <select class="form-control" name="land_id[]" id="area" multiple
                                    onchange="updateLatLon(this)">
                                    <option value="">Select</option>
                                    @foreach ($records as $record)
                                        <option value="{{ $record->id }}" data-demarcation='@json($record->demarcation_array)'>
                                            {{ $record->farmer->name ?? 'Unknown User' }} - {{ $record->location }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Hidden textarea for demarcation_points -->
                            {{-- <textarea name="demarcation_points" id="demarcation_points" cols="30" rows="10" hidden></textarea> --}}
                            <input type="hidden" id="demarcation_points_map" name="demarcation_points_map" />

                            <!-- Hidden input for ndvi -->
                            <input type="hidden" id="ndvi" name="ndvi" value="">



                            {{-- <div class="col-md-6">
                                <label for="b8">B8 (NIR)</label>
                                <input type="number" max="999999999999999" step="0.0001" class="form-control"
                                    id="b8" name="b8">
                                <div class="text-danger b8-error mt-1"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="b4">B4 (Red)</label>
                                <input type="number" max="999999999999999" step="0.0001" class="form-control"
                                    id="b4" name="b4">
                                <div class="text-danger b4-error mt-1"></div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label for="ndvi">Calculated NDVI</label>
                                <input type="text" class="form-control" id="ndvi" name="ndvi" readonly>
                            </div> --}}
                        </div>
                        {{-- <button type="button" class="btn btn-info btn-sm mt-3" onclick="calculateNDVI()">Calculate
                            NDVI</button> --}}
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <a class="btn btn-primary mb-2" href="{{ route('insurance.type.index') }}">Back</a>
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4 class="mb-0">{{ $InsuranceType->name }}</h4>
                                    <div class="mt-2">
                                        <p class="mt-2 mb-0 text-danger" style="font-style: italic; font-size: 14px;">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <strong>Note:</strong> After saving NDVI data, results may not appear instantly
                                            due to satellite processing time.
                                            Sometimes, all results load after a single refresh; other times, data may appear
                                            gradually.
                                            If the table seems empty or incomplete, don't worry — your data is being
                                            processed.
                                            Please refresh the page periodically to view the latest results.
                                        </p>
                                    </div>
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
                                            <th>Date</th>
                                            <th>Area</th>
                                            {{-- <th>B4 Value</th> --}}
                                            {{-- <th>B8 Value</th> --}}
                                            <th>NDVI</th>
                                            <th>Vegetation Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($InsuranceSubTypes as $InsuranceSubType)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $InsuranceSubType->date ?? '-' }}</td>
                                                <td>{{ $InsuranceSubType->land->farmer->name ?? '-' }} -
                                                    {{ $InsuranceSubType->land->location ?? '-' }}</td>
                                                {{-- <td>{{ $InsuranceSubType->b4 ?? '-' }}</td> --}}
                                                {{-- <td>{{ $InsuranceSubType->b8 ?? '-' }}</td> --}}
                                                <td>{{ $InsuranceSubType->ndvi ? round($InsuranceSubType->ndvi, 2) . '%' : '-' }}
                                                </td>
                                                <td>
                                                    @if ($InsuranceSubType->ndvi < 0.4)
                                                        <span class="badge badge-danger">Poor</span>
                                                    @else
                                                        <span class="badge badge-success">Healthy</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Types' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form
                                                                action="{{ route('insurance.sub.type.satelliteNDVI.destroy', $InsuranceSubType->id) }}"
                                                                method="POST" style="display:inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="incurance_type_id"
                                                                    value="{{ $InsuranceType->id }}">
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-danger show_subType_confirm">Delete</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#area').select2({
                placeholder: 'Select Area(s)',
                width: '100%'
            });
        });
    </script>

    @if (session('success'))
        <script>
            $(document).ready(function() {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true
                };
                toastr.success("{{ session('success') }}");
            });
        </script>
    @endif
    <script>
        function updateLatLon(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const demarcation = selectedOption.getAttribute('data-demarcation');

            if (demarcation) {
                document.getElementById('demarcation_points').value = demarcation;
            } else {
                document.getElementById('demarcation_points').value = '';
            }
        }


        $(document).ready(function() {
            // Set min and max date to current year only
            const today = new Date();
            const year = today.getFullYear();
            const minDate = `${year}-01-01`;
            const maxDate = `${year}-12-31`;

            $("input[name='date']").attr("min", minDate);
            $("input[name='date']").attr("max", maxDate);

            $('#table_id_events').DataTable();

            $('.show_subType_confirm').click(function(event) {
                event.preventDefault();
                const form = $(this).closest("form");
                swal({
                    title: `Are you sure you want to delete this record?`,
                    text: "If you delete this, it will be gone forever.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
            });
        });

        function calculateNDVI() {
            const b8 = parseFloat(document.getElementById("b8").value);
            const b4 = parseFloat(document.getElementById("b4").value);

            if (isNaN(b8) || isNaN(b4) || (b8 + b4) === 0) {
                alert("Please enter B8 and B4 values.");
                return;
            }

            const ndvi = (b8 - b4) / (b8 + b4);
            document.getElementById("ndvi").value = ndvi.toFixed(4);
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#ndviForm').on('submit', function(e) {
            let isValid = true;

            // Clear previous errors
            $('.date-error').html('');
            if ($('.area-error').length === 0) {
                $('#area').after('<div class="text-danger area-error mt-1"></div>');
            }
            $('.area-error').html('');

            const dateValue = $('#ndvi_date').val();
            const areaValue = $('#area').val();

            if (!dateValue) {
                $('.date-error').html('Date is required.');
                isValid = false;
            }

            if (!areaValue || areaValue.length === 0) {
                $('.area-error').html('At least one area must be selected.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault(); // Stop form from submitting
            }
        });

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error("{{ $error }}");
            @endforeach
        @endif

        // Select All checkbox for area dropdown
        $('#select_all_areas').on('change', function() {
            const allOptions = $('#area option:not([value=""])');
            if ($(this).is(':checked')) {
                allOptions.prop('selected', true);
            } else {
                allOptions.prop('selected', false);
            }

            $('#area').trigger('change'); // Refresh Select2
        });
    </script>
    <script>
        function updateLatLon(selectElement) {
            const selectedOptions = Array.from(selectElement.selectedOptions);
            const demarcationMap = {};

            selectedOptions.forEach(option => {
                const landId = option.value;
                const demarcation = option.dataset.demarcation;

                if (landId && demarcation) {
                    demarcationMap[landId] = JSON.parse(demarcation);
                }
            });

            // Set to hidden input
            document.getElementById('demarcation_points_map').value = JSON.stringify(demarcationMap);
        }
    </script>

@endsection
