@extends('admin.layout.app')
@section('title', 'Sub Admins')
@section('content')

    @foreach ($subAdmins as $subAdmin)
        <div class="modal fade" id="createSubadminModal-{{ $subAdmin->id }}" tabindex="-1" role="dialog"
            aria-labelledby="permissionModalLabel-{{ $subAdmin->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="permissionModalLabel-{{ $subAdmin->id }}">Assign Permissions</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('update.permissions', [$subAdmin->id]) }}"
                            id="createSubadminForm-{{ $subAdmin->id }}" enctype="multipart/form-data" method="POST">
                            @csrf
                            @method('POST')
                            <div class="row">
                                <input type="hidden" id="sub_admin_id" name="sub_admin_id" value="{{ $subAdmin->id }}">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        @foreach ($sideMenus as $sideMenu)
                                            <!-- Parent Menu Checkbox -->
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input parent-checkbox"
                                                    id="menu-{{ $subAdmin->id }}-{{ $sideMenu->id }}"
                                                    onclick="toggleNestedPermissions(this, '{{ $subAdmin->id }}-{{ $sideMenu->id }}')"
                                                    {{ $subAdmin->permissions->where('side_menu_id', $sideMenu->id)->isNotEmpty() ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="menu-{{ $subAdmin->id }}-{{ $sideMenu->id }}">
                                                    {{ $sideMenu->name }}
                                                </label>
                                            </div>

                                            <!-- Nested Permissions (Initially Hidden) -->
                                            <div class="ml-4 nested-permissions align-items-center"
                                                id="nested-permissions-{{ $subAdmin->id }}-{{ $sideMenu->id }}"
                                                style="{{ $subAdmin->permissions->where('side_menu_id', $sideMenu->id)->isNotEmpty() ? 'display:flex;' : 'display:none;' }}">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                        id="view-{{ $subAdmin->id }}-{{ $sideMenu->id }}"
                                                        name="side_menu_id[{{ $sideMenu->id }}][]" value="view"
                                                        {{ $subAdmin->permissions->where('side_menu_id', $sideMenu->id)->pluck('permissions')->contains('view') ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="view-{{ $subAdmin->id }}-{{ $sideMenu->id }}">View</label>
                                                </div>

                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                        id="create-{{ $subAdmin->id }}-{{ $sideMenu->id }}"
                                                        name="side_menu_id[{{ $sideMenu->id }}][]" value="create"
                                                        {{ $subAdmin->permissions->where('side_menu_id', $sideMenu->id)->pluck('permissions')->contains('create') ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="create-{{ $subAdmin->id }}-{{ $sideMenu->id }}">Create</label>
                                                </div>

                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                        id="edit-{{ $subAdmin->id }}-{{ $sideMenu->id }}"
                                                        name="side_menu_id[{{ $sideMenu->id }}][]" value="edit"
                                                        {{ $subAdmin->permissions->where('side_menu_id', $sideMenu->id)->pluck('permissions')->contains('edit') ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="edit-{{ $subAdmin->id }}-{{ $sideMenu->id }}">Edit</label>
                                                </div>

                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                        id="delete-{{ $subAdmin->id }}-{{ $sideMenu->id }}"
                                                        name="side_menu_id[{{ $sideMenu->id }}][]" value="delete"
                                                        {{ $subAdmin->permissions->where('side_menu_id', $sideMenu->id)->pluck('permissions')->contains('delete') ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="delete-{{ $subAdmin->id }}-{{ $sideMenu->id }}">Delete</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer justify-content-center">
                                <button type="submit" class="btn btn-primary"
                                    onclick="submitSubadminForm({{ $subAdmin->id }})">Save Permissions</button>
                            </div>
                        </form>
                    </div>
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
                                    <h4>Sub Admins</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">

                                @if (Auth::guard('admin')->check())
                                    <a class="btn btn-primary mb-3 text-white"
                                        href="{{ route('subadmin.create') }}">Create</a>
                                @endif

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Image</th>
                                            <th scope="col">Permissions</th>
                                            <th>Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subAdmins as $subAdmin)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $subAdmin->name }}</td>
                                                <td><a href="mailto:{{ $subAdmin->email }}">{{ $subAdmin->email }}</a></td>
                                                <td>{{ $subAdmin->phone }}</td>
                                                <td>
                                                    <img src="{{ asset($subAdmin->image) }}" alt="" height="50"
                                                        width="50" class="image">
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <button class="btn btn-info text-white updatePermissionBtn"
                                                            data-toggle="modal"
                                                            data-target="#createSubadminModal-{{ $subAdmin->id }}"><i
                                                                class="fas fa-user"></i></button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label class="custom-switch p-0">
                                                        <input type="checkbox" class="custom-switch-input toggle-status"
                                                            data-id="{{ $subAdmin->id }}"
                                                            {{ $subAdmin->status == 1 ? 'checked' : '' }}>
                                                        <span class="custom-switch-indicator"></span>
                                                        <span class="custom-switch-description">
                                                            {{ $subAdmin->status == 1 ? 'Activated' : 'Deactivated' }}
                                                        </span>
                                                    </label>
                                                </td>

                                                <td>
                                                    @if (Auth::guard('admin')->check())
                                                        <div class="d-flex gap-4">
                                                            <a href="{{ route('subadmin.edit', $subAdmin->id) }}"
                                                                class="btn btn-primary">Edit</a>
                                                            <form action="{{ route('subadmin.destroy', $subAdmin->id) }}"
                                                                method="POST"
                                                                style="display:inline-block; margin-left: 10px">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-danger btn-flat show_confirm"
                                                                    data-toggle="tooltip">Delete</button>
                                                            </form>
                                                        </div>
                                                    @endif
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
        function toggleNestedPermissions(parentCheckbox, uniqueId) {
            const nestedPermissions = document.getElementById(`nested-permissions-${uniqueId}`);
            if (parentCheckbox.checked) {
                // Show nested permissions and enable child checkboxes
                nestedPermissions.style.display = 'flex';
                nestedPermissions.querySelectorAll('.form-check-input').forEach(checkbox => {
                    checkbox.disabled = false;
                });
            } else {
                // Hide nested permissions and disable child checkboxes
                nestedPermissions.style.display = 'none';
                nestedPermissions.querySelectorAll('.form-check-input').forEach(checkbox => {
                    checkbox.disabled = true;
                    checkbox.checked = false; // Uncheck all child boxes
                });
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.toggle-status').on('change', function() {
                var subAdminId = $(this).data('id');
                var status = $(this).is(':checked') ? 1 : 0;
                var toggleSwitch = $(this);
                var statusText = $(this).siblings('.custom-switch-description');

                $.ajax({
                    url: "{{ route('subadmin.StatusChange') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: subAdminId,
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            statusText.text(status ? 'Activated' : 'Deactivated');
                            toastr.success('Status Updated Successfully');

                        } else {
                            alert('Something went wrong!');
                            toggleSwitch.prop('checked', !status); // Revert on failure
                        }
                    },
                    error: function() {
                        alert('Failed to update status');
                        toggleSwitch.prop('checked', !status); // Revert on error
                    }
                });
            });
        });
    </script>

@endsection
