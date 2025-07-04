@extends('admin.layout.app')
@section('title', 'Insurance Companies')
@section('content')

    {{-- Add Insurance Company Modal --}}
    <div class="modal fade" id="InsuranceCompaniesModal" tabindex="-1" role="dialog"
        aria-labelledby="InsuranceCompaniesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Insurance Company</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="CreateForm" action="{{ route('insurance.company.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            {{-- Company Name --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Company Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                    >
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Company Email --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Company Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                        >
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Image --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Image (Optional)</label>
                                    <input type="file" name="image" class="form-control">
                                    @error('image')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status (Optional)</label>
                                    <select name="status" class="form-control">
                                        <option disabled {{ old('status') === null ? 'selected' : '' }}>Select an Option
                                        </option>
                                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Deactive
                                        </option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="CreateBtn" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modals --}}
    @foreach ($insuranceCompanies as $insuranceCompany)
        <div class="modal fade" id="EditInsuranceCompaniesModal-{{ $insuranceCompany->id }}" tabindex="-1" role="dialog"
            aria-labelledby="EditInsuranceCompaniesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Insurance Company</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form class="EditForm" action="{{ route('insurance.company.update', $insuranceCompany->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <div class="row">
                                {{-- Name --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Name</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $insuranceCompany->name) }}">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Email</label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email', $insuranceCompany->email) }}">
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Image --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Image (Optional)</label>
                                        <input type="file" name="image" class="form-control">
                                        @error('image')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @if ($insuranceCompany->image)
                                            <div class="mt-2">
                                                <img src="{{ asset($insuranceCompany->image) }}" alt="Image"
                                                    width="100">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status (Optional)</label>
                                        <select name="status" class="form-control" required>
                                            <option disabled
                                                {{ old('status', $insuranceCompany->status) === null ? 'selected' : '' }}>
                                                Select an Option</option>
                                            <option value="1"
                                                {{ old('status', $insuranceCompany->status) == '1' ? 'selected' : '' }}>
                                                Active</option>
                                            <option value="0"
                                                {{ old('status', $insuranceCompany->status) == '0' ? 'selected' : '' }}>
                                                Deactive</option>
                                        </select>
                                        @error('status')
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

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Insurance Companies</h4>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                @if (Auth::guard('admin')->check() ||
                                        $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Companies' &&
                                                $permission['permissions']->contains('create')))
                                    <a class="btn btn-primary mb-3 text-white" href="#" data-toggle="modal"
                                        data-target="#InsuranceCompaniesModal">Create Company</a>
                                @endif
                                <table class="table" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Image</th>
                                            <th>Policies</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($insuranceCompanies as $insuranceCompany)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $insuranceCompany->name }}</td>
                                                <td><a
                                                        href="mailto:{{ $insuranceCompany->email }}">{{ $insuranceCompany->email }}</a>
                                                </td>
                                                <td>
                                                    <img src="{{ asset($insuranceCompany->image) }}" width="50"
                                                        height="50" class="image">
                                                </td>
                                                <td>
                                                    <a class="btn btn-primary"
                                                        href="{{ route('company.insurance.types.index', $insuranceCompany->id) }}">View</a>
                                                </td>
                                                <td>
                                                    @if ($insuranceCompany->status == 1)
                                                        <span class="badge badge-success">Activated</span>
                                                    @else
                                                        <span class="badge badge-danger">Deactivated</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Companies' &&
                                                                        $permission['permissions']->contains('edit')))
                                                            <a class="btn btn-primary text-white" data-toggle="modal"
                                                                data-target="#EditInsuranceCompaniesModal-{{ $insuranceCompany->id }}">Edit</a>
                                                        @endif

                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Companies' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form
                                                                action="{{ route('insurance.company.destroy', $insuranceCompany->id) }}"
                                                                method="POST" class="ml-2">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-danger show_confirm">Delete</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- end card body -->
                        </div> <!-- end card -->
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#table_id_events').DataTable();

            $('.show_confirm').click(function(event) {
                event.preventDefault();
                const form = $(this).closest("form");

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    @if ($errors->has('name'))
        <script>
            toastr.error("{{ $errors->first('name') }}", 'Validation Error', {
                timeOut: 5000
            });
        </script>
    @endif

    <script>
        $('#CreateForm').on('submit', function(e) {
            let form = $(this);
            let isValid = true;

            const nameField = form.find('input[name="name"]');
            const name = nameField.val().trim();

            const emailField = form.find('input[name="email"]');
            const email = emailField.val().trim();

            form.find('.text-danger').remove();

            if (name === '') {
                nameField.after('<span class="text-danger">The Company name is required.</span>');
                isValid = false;
            }

            if (email === '') {
                emailField.after('<span class="text-danger">The Company email is required.</span>');
                isValid = false;
            }

            if (!isValid) e.preventDefault();
        });


        $(document).ready(function() {
            $('.EditForm').each(function() {
                const form = $(this);

                form.on('submit', function(e) {
                    let isValid = true;

                    const nameField = form.find('input[name="name"]');
                    const name = nameField.val().trim();

                    const emailField = form.find('input[name="email"]');
                    const email = emailField.val().trim();

                    // Clear old errors
                    form.find('.text-danger').remove();

                    if (name === '') {
                        nameField.after(
                            '<span class="text-danger">The Company name is required.</span>'
                        );
                        isValid = false;
                    }

                     if (email === '') {
                        emailField.after(
                            '<span class="text-danger">The Company email is required.</span>'
                        );
                        isValid = false;
                    }

                    if (!isValid) e.preventDefault();
                });

                form.find('input[name="name"]').on('input', function() {
                    $(this).next('.text-danger').remove();
                });
            });
        });
    </script>
@endsection
