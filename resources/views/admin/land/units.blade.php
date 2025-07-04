@extends('admin.layout.app')
@section('title', 'Land Units')
@section('content')

    {{-- Add unit Modal --}}
    <div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unitModalLabel">Create Unit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="CreateForm" action="{{ route('unit.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Message Textbox -->
                        <div class="form-group">
                            <label for="message">Unit Name</label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit') }}">
                            @error('unit')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                
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



    {{-- Edit unit Modal --}}
    @foreach ($units as $land)
        <div class="modal fade" id="editunitModal-{{ $land->id }}" tabindex="-1" role="dialog"
            aria-labelledby="unitModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="unitModalLabel">Edit Unit</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form class="EditForm" action="{{ route('unit.update', $land->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <!-- Message Textbox -->
                            <div class="form-group">
                                <label for="message">Unit Name</label>
                                <input type="text" name="unit" class="form-control" value="{{ $land->unit }}">
                                @error('unit')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
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
                        <a class="btn btn-primary mb-4" href="{{ route('land.index') }}">Back</a>
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Land Units</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                @if (Auth::guard('admin')->check() ||
                                        $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Land Data Management' &&
                                                $permission['permissions']->contains('create')))
                                    <a class="btn btn-primary mb-3" href="#" data-toggle="modal"
                                        data-target="#unitModal">Add Unit</a>
                                @endif

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($units as $land)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $land->unit }}</td>
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Land Data Management' &&
                                                                        $permission['permissions']->contains('edit')))
                                                            <a href="#" data-toggle="modal"
                                                                data-target="#editunitModal-{{ $land->id }}"
                                                                class="btn btn-primary">Edit</a>
                                                        @endif

                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Land Data Management' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form action="{{ route('unit.destroy', $land->id) }}"
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
<script>
    $(document).ready(function () {
        $('#table_id_events').DataTable();

        // Validation for Add Unit
        $('#unitModal form').on('submit', function (e) {
            const unitInput = $(this).find('input[name="unit"]');
            const errorBox = $('#unitValidationErrors');
            const unitValue = unitInput.val().trim();

            errorBox.addClass('d-none').html('');

            if (unitValue === '') {
                e.preventDefault();
                errorBox.removeClass('d-none').html('<ul><li>Unit name is required.</li></ul>');
            }
        });

        // Validation for Edit Unit - Attach to each edit modal
        @foreach ($units as $land)
            $('#editunitModal-{{ $land->id }} form').on('submit', function (e) {
                const unitInput = $(this).find('input[name="unit"]');
                const errorBox = $('#editUnitValidationErrors-{{ $land->id }}');
                const unitValue = unitInput.val().trim();

                errorBox.addClass('d-none').html('');

                if (unitValue === '') {
                    e.preventDefault();
                    errorBox.removeClass('d-none').html('<ul><li>Unit name is required.</li></ul>');
                }
            });
        @endforeach

        // Delete confirmation
        $('.show_confirm').click(function (event) {
            var form = $(this).closest("form");
            event.preventDefault();
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
</script>

@if ($errors->has('name'))
        <script>
            toastr.error("{{ $errors->first('unit') }}", 'Validation Error', {
                timeOut: 5000
            });
        </script>
    @endif

    <script>
        $(document).ready(function() {
            // ✅ Form validation for Create District
            $('#CreateForm').on('submit', function(e) {
                let form = $(this);
                let isValid = true;

                const nameField = form.find('input[name="unit"]');
                const name = nameField.val().trim();

                // Clear old error
                form.find('.text-danger').remove();

                if (name === '') {
                    nameField.after('<span class="text-danger">The Unit name is required.</span>');
                    isValid = false;
                }

                if (!isValid) e.preventDefault();
            });

            // ✅ Remove error on input
            $('#CreateForm input[name="unit"]').on('input', function() {
                $(this).next('.text-danger').remove();
            });
        });

        $(document).ready(function() {
            $('.EditForm').each(function() {
                const form = $(this);

                form.on('submit', function(e) {
                    let isValid = true;

                    const nameField = form.find('input[name="unit"]');
                    const name = nameField.val().trim();

                    // Clear old errors
                    form.find('.text-danger').remove();

                    if (name === '') {
                        nameField.after(
                            '<span class="text-danger">The Unit name is required.</span>');
                        isValid = false;
                    }

                    if (!isValid) e.preventDefault();
                });

                form.find('input[name="unit"]').on('input', function() {
                    $(this).next('.text-danger').remove();
                });
            });
        });
    </script>

@endsection

