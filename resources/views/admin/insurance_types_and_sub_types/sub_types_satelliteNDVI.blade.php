@extends('admin.layout.app')
@section('title', 'Satellite Index (NDVI)')
@section('content')

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


                            <div class="col-md-6">
                                <label for="b8">B8 (NIR)</label>
                                <input type="number" max="999999999999999" step="0.0001" class="form-control"
                                    id="b8" name="b8">
                                <div class="text-danger b8-error mt-1"></div>
                            </div>

                            <div class="col-md-6 mt-3">
                                <label for="b4">B4 (Red)</label>
                                <input type="number" max="999999999999999" step="0.0001" class="form-control"
                                    id="b4" name="b4">
                                <div class="text-danger b4-error mt-1"></div> 
                            </div>


                            <div class="col-md-6 mt-3">
                                <label for="ndvi">Calculated NDVI</label>
                                <input type="text" class="form-control" id="ndvi" name="ndvi" readonly>
                            </div>
                        </div>
                        <button type="button" class="btn btn-info btn-sm mt-3" onclick="calculateNDVI()">Calculate
                            NDVI</button>
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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">{{ $InsuranceType->name }}</h4>
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
                                            <th>B4 Value</th>
                                            <th>B8 Value</th>
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
                                                <td>{{ $InsuranceSubType->b4 ?? '-' }}</td>
                                                <td>{{ $InsuranceSubType->b8 ?? '-' }}</td>
                                                <td>{{ $InsuranceSubType->ndvi ?? '-' }}</td>
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

        // Auto-fetch NDVI data on date change
        // $("input[name='date']").on("change", function() {
        //     const selectedDate = $(this).val();
        //     const insuranceTypeId = $("input[name='incurance_type_id']").val();

        //     // console.log("Date changed:", selectedDate);

        //     if (!selectedDate) return;

        //     $.ajax({
        //         url: "{{ route('ndvi.fetch') }}",
        //         method: "GET",
        //         data: {
        //             date: selectedDate,
        //             insurance_type_id: insuranceTypeId
        //         },
        //         success: function(response) {
        //             console.log("NDVI response:", response);
        //             $("#b4").val(response.b4);
        //             $("#b8").val(response.b8);
        //             $("#ndvi").val(response.ndvi);
        //         },
        //         error: function(xhr, status, error) {
        //             console.error("NDVI error:", error);
        //             console.log("Status:", status);
        //             console.log("Response:", xhr.responseText);
        //             alert("Failed to fetch NDVI data. Try again.");
        //             $("#b4").val('');
        //             $("#b8").val('');
        //             $("#ndvi").val('');
        //         }
        //     });
        // });
    </script>
    <script>
        $('#ndviForm').on('submit', function(e) {
            let isValid = true;

            const dateInput = $('#ndvi_date');
            const dateValue = dateInput.val();

            const b8Input = $('#b8');
            const b8Value = b8Input.val();

            const b4Input = $('#b4');
            const b4Value = b4Input.val();

            // Clear all previous errors
            $('.date-error').html('');
            $('.b8-error').html('');
            $('.b4-error').html('');

            if (!dateValue) {
                $('.date-error').html('Date is required.');
                isValid = false;
            }

            if (!b8Value) {
                $('.b8-error').html('B8 is required.');
                isValid = false;
            }

            if (!b4Value) {
                $('.b4-error').html('B4 is required.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault(); // Prevent form submission
            }
        });
    </script>
@endsection
