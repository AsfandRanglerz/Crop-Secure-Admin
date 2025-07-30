@extends('admin.layout.app')
@section('title', 'Insured Crops')
@section('content')


    {{-- Add Ensured Crops Modal --}}
    <div class="modal fade" id="EnsuredCropModal" tabindex="-1" role="dialog" aria-labelledby="EnsuredCropModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="EnsuredCropModalLabel">Create Insured Crop</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="CreateForm" action="{{ route('ensured.crop.name.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-group mb-0">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group mb-0">
                                    <label for="sum">Sum Insured</label>
                                    <div class="input-group">
                                        <input type="number" name="sum" id="sumField" class="form-control"
                                            value="{{ old('sum') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text font-weight-bold"
                                                style="border: 2px solid #cbd2d8;">PKR</span>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        (Note: The sum insured value is applied to 100% benchmark against 1 acre)
                                    </small>
                                    {{-- Validation Error Placeholder --}}
                                    <div id="sumError"></div>
                                </div>
                            </div>


                        </div>


                        <div class="form-group">
                            <label>Crop Start Period</label>
                            <div class="row">
                                <div class="col">
                                    <select name="harvest_start" class="form-control">
                                        <option value="" disabled selected>Start Month</option>
                                        @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger harvest_start_error"></div>
                                </div>
                                <div class="col">
                                    <select name="harvest_end" class="form-control">
                                        <option value="" disabled selected>End Month</option>
                                        @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger harvest_end_error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Insurance Purchase Period</label>
                            <div class="row">
                                <div class="col">
                                    <select name="insurance_start" class="form-control">
                                        <option value="" disabled selected>Start Month</option>
                                        @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger insurance_start_error"></div>
                                </div>
                                <div class="col">
                                    <select name="insurance_end" class="form-control">
                                        <option value="" disabled selected>End Month</option>
                                        @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger insurance_end_error"></div>
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



    {{-- Edit Ensured Crops Modal --}}
    @foreach ($EnsuredCropNames as $EnsuredCrop)
        <div class="modal fade" id="EditEnsuredCropModal-{{ $EnsuredCrop->id }}" tabindex="-1" role="dialog"
            aria-labelledby="EditEnsuredCropModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="EditEnsuredCropModalLabel">Edit Insured Crop</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('ensured.crop.name.update', $EnsuredCrop->id) }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $EnsuredCrop->name) }}">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group mb-0">
                                        <label for="sum">Sum Insured</label>
                                        <div class="input-group">
                                            <input type="number" name="sum"
                                                id="editSumField-{{ $EnsuredCrop->id }}" class="form-control"
                                                value="{{ old('sum', $EnsuredCrop->sum_insured_value) }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text font-weight-bold"
                                                    style="border: 2px solid #cbd2d8;">PKR</span>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1">(Note: The sum insured value is applied to
                                            100% benchmark against 1 acre)</small>
                                        <div id="editSumError-{{ $EnsuredCrop->id }}">
                                            @error('sum')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group">
                                <label>Crop Start Period</label>
                                <div class="d-flex">
                                    <select name="harvest_start" class="form-control">
                                        @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}"
                                                {{ old('harvest_start', $EnsuredCrop->harvest_start_time) == $month ? 'selected' : '' }}>
                                                {{ $month }}</option>
                                        @endforeach
                                    </select>
                                    <span class="mx-2"></span>
                                    <select name="harvest_end" class="form-control">
                                        @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}"
                                                {{ old('harvest_end', $EnsuredCrop->harvest_end_time) == $month ? 'selected' : '' }}>
                                                {{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Insurance Purchase Period</label>
                                <div class="d-flex">
                                    <select name="insurance_start" class="form-control">
                                        @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}"
                                                {{ old('insurance_start', $EnsuredCrop->insurance_start_time) == $month ? 'selected' : '' }}>
                                                {{ $month }}</option>
                                        @endforeach
                                    </select>
                                    <span class="mx-2"></span>
                                    <select name="insurance_end" class="form-control">
                                        @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}"
                                                {{ old('insurance_end', $EnsuredCrop->insurance_end_time) == $month ? 'selected' : '' }}>
                                                {{ $month }}</option>
                                        @endforeach
                                    </select>
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
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Insured Crops</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                @if (Auth::guard('admin')->check() ||
                                        $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insured Crops' &&
                                                $permission['permissions']->contains('create')))
                                    <a class="btn btn-primary mb-3 text-white" href="#" data-toggle="modal"
                                        data-target="#EnsuredCropModal">Create</a>
                                @endif

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Sum Insured</th>
                                            <th>Crop Start</th>
                                            <th>Crop End</th>
                                            <th>Insurance Start</th>
                                            <th>Insurance End</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($EnsuredCropNames as $EnsuredCrop)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $EnsuredCrop->name }}</td>
                                                <td>{{ number_format($EnsuredCrop->sum_insured_value) }} PKR</td>
                                                <td>{{ $EnsuredCrop->harvest_start_time }} {{ $currentYear }}</td>
                                                <td>{{ $EnsuredCrop->harvest_end_time }} {{ $currentYear }}</td>
                                                <td>{{ $EnsuredCrop->insurance_start_time }} {{ $currentYear }}</td>
                                                <td>{{ $EnsuredCrop->insurance_end_time }} {{ $currentYear }}</td>
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insured Crops' &&
                                                                        $permission['permissions']->contains('edit')))
                                                            <a href="#" class="btn btn-primary" data-toggle="modal"
                                                                data-target="#EditEnsuredCropModal-{{ $EnsuredCrop->id }}">Edit</a>
                                                        @endif

                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insured Crops' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form
                                                                action="
                                                            {{ route('ensured.crop.name.destroy', $EnsuredCrop->id) }}
                                                            "
                                                                method="POST"
                                                                style="display:inline-block; margin-left: 10px">
                                                                @csrf
                                                                @method('DELETE')
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#table_id_events').DataTable();

            $(document).on('click', '.show_confirm', function(event) {
                event.preventDefault();

                var form = $(this).closest("form");

                swal({
                    title: "Are you sure you want to delete this record?",
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
    </script>
    <script>
        $(document).ready(function() {

            function validateForm(form) {
                let isValid = true;

                const nameField = form.find('input[name="name"]');
                const sumField = $('input[name="sum"]');
                const sumError = $('#sumError');
                const harvestStart = form.find('select[name="harvest_start"]');
                const harvestEnd = form.find('select[name="harvest_end"]');
                const insuranceStart = form.find('select[name="insurance_start"]');
                const insuranceEnd = form.find('select[name="insurance_end"]');

                // Remove previous errors
                form.find('.text-danger').remove();
                sumError.html('');

                // Validate name
                if (nameField.val().trim() === '') {
                    nameField.after('<span class="text-danger">The name field is required.</span>');
                    isValid = false;
                }


                // Validate sum
                if (sumField.val().trim() === '') {
                    sumError.html('<span class="text-danger">The sum insured field is required.</span>');
                    isValid = false;
                }


                // Validate dropdowns
                if (!harvestStart.val()) {
                    harvestStart.after('<span class="text-danger">Select a start month.</span>');
                    isValid = false;
                }

                if (!harvestEnd.val()) {
                    harvestEnd.after('<span class="text-danger">Select an end month.</span>');
                    isValid = false;
                }

                if (!insuranceStart.val()) {
                    insuranceStart.after('<span class="text-danger">Select a start month.</span>');
                    isValid = false;
                }

                if (!insuranceEnd.val()) {
                    insuranceEnd.after('<span class="text-danger">Select an end month.</span>');
                    isValid = false;
                }

                return isValid;
            }

            // ✅ CREATE form validation
            $('#CreateForm').on('submit', function(e) {
                if (!validateForm($(this))) {
                    e.preventDefault();
                }
            });

            // ✅ Input change listeners on CREATE form
            $('#CreateForm input, #CreateForm select').on('input change', function() {
                $(this).next('.text-danger').remove();
            });

            // ✅ All EDIT forms validation
            $('[id^="EditEnsuredCropModal-"]').each(function() {
                const form = $(this).find('form');
                const cropId = form.find('input[name="sum"]').attr('id').split('-')[1];

                form.on('submit', function(e) {
                    let isValid = true;

                    const nameField = form.find('input[name="name"]');
                    const sumField = form.find('input[name="sum"]');

                    const name = nameField.val().trim();
                    const sum = sumField.val().trim();

                    // Clear old errors
                    form.find('.text-danger').remove();

                    // ✅ Validate name
                    if (name === '') {
                        nameField.after(
                            '<span class="text-danger">The name field is required.</span>');
                        isValid = false;
                    }

                    // ✅ Validate sum
                    const sumErrorContainer = $('#editSumError-' + cropId);
                    sumErrorContainer.html(''); // Clear previous
                    if (sum === '') {
                        sumErrorContainer.html(
                            '<span class="text-danger">The sum insured field is required.</span>'
                            );
                        isValid = false;
                    }

                    if (!isValid) e.preventDefault();
                });

                // ✅ Clear error on input
                form.find('input[name="name"]').on('input', function() {
                    $(this).next('.text-danger').remove();
                });

                form.find('input[name="sum"]').on('input', function() {
                    const cropId = $(this).attr('id').split('-')[1];
                    $('#editSumError-' + cropId).html('');
                });
            });

        });
    </script>


@endsection
