@extends('admin.layout.app')
@section('title', 'Dealer Items')
@section('content')


    {{-- Add Items Modal --}}
    <div class="modal fade" id="ItemsModal" tabindex="-1" role="dialog" aria-labelledby="ItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ItemsModalLabel">Create Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="CreateForm" action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" class="form-control" >
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="image">Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*" >
                                    @error('image')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="description">Description (Optional)</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" >Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Edit Items Modal --}}
    @foreach ($Items as $Item)
        <div class="modal fade" id="EditItemsModal-{{ $Item->id }}" tabindex="-1" role="dialog"
            aria-labelledby="EditItemsModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="EditItemsModalLabel">Edit Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('item.update', $Item->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" id=""
                                            value="{{ ucfirst($Item->name) }}" class="form-control">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="image">Image</label>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                        @if ($Item->image)
                                            <img src="{{ asset('public/' . $Item->image) }}" width="60" height="60"
                                                class="mt-2" style="object-fit:cover;">
                                        @endif
                                        @error('image')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ $Item->description }}</textarea>
                                        @error('description')
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
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Dealer Items</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                @if (Auth::guard('admin')->check() ||
                                        $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Dealer Items' &&
                                                $permission['permissions']->contains('create')))
                                    <a class="btn btn-primary mb-3 text-white" href="#" data-toggle="modal"
                                        data-target="#ItemsModal">Create Item</a>
                                @endif

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Image</th>
                                            <th>Description</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($Items as $Item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ ucfirst($Item->name) }}</td>

                                                <!-- ✅ New Image Column -->
                                                <td>
                                                    @if ($Item->image)
                                                        <img src="{{ asset('public/' . $Item->image) }}" alt="Item Image"
                                                            width="60" height="60"
                                                            style="border-radius: 5px; object-fit: cover;">
                                                    @else
                                                        <span>No Image</span>
                                                    @endif
                                                </td>
                                                <td>{{ \Illuminate\Support\Str::limit($Item->description, 100) }}</td>


                                                <!-- Existing Actions Column -->
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Dealer Items' &&
                                                                        $permission['permissions']->contains('edit')))
                                                            <a class="btn btn-primary text-white" href="#"
                                                                data-toggle="modal"
                                                                data-target="#EditItemsModal-{{ $Item->id }}">Edit</a>
                                                        @endif

                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Dealer Items' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form action="{{ route('item.destroy', $Item->id) }}"
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
    
    {{-- ✅ Name Field Error Toaster --}}
    @if ($errors->has('name'))
        <script>
            toastr.error("{{ $errors->first('name') }}", 'Validation Error', { timeOut: 5000 });
        </script>
    @endif
<script>
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            toastr.error("{{ $error }}");
        @endforeach
    @endif
</script>
<script>
    $(document).ready(function () {

        // Validate Create Form
        function validateCreateForm() {
            let isValid = true;

            const nameField = $('#CreateForm input[name="name"]');
            const imageField = $('#CreateForm input[name="image"]');
            const submitBtn = $('#CreateBtn');

            const name = nameField.val().trim();
            const image = imageField[0].files.length;

            // Remove previous errors
            nameField.next('.text-danger').remove();
            imageField.next('.text-danger').remove();

            // Validate name
            if (name === '') {
                nameField.after('<span class="text-danger">The name field is required.</span>');
                isValid = false;
            }

            // Validate image
            if (!image) {
                imageField.after('<span class="text-danger">The image field is required.</span>');
                isValid = false;
            }

            // Enable or disable submit button
            submitBtn.prop('disabled', !isValid);
            return isValid;
        }

        $('#CreateForm').on('submit', function (e) {
            if (!validateCreateForm()) {
                e.preventDefault();
            }
        });

        $('#CreateForm input[name="name"], #CreateForm input[name="image"]').on('input change', function () {
            $(this).next('.text-danger').remove();
            validateCreateForm();
        });

        // ✅ Validate all Edit forms
        $('[id^="EditItemsModal-"]').each(function () {
            const form = $(this).find('form');

            form.on('submit', function (e) {
                const nameField = $(this).find('input[name="name"]');
                const imageField = $(this).find('input[name="image"]');

                let isValid = true;

                const name = nameField.val().trim();

                // Remove old messages
                nameField.next('.text-danger').remove();
                imageField.next('.text-danger').remove();

                // Validate name
                if (name === '') {
                    nameField.after('<span class="text-danger">The name field is required.</span>');
                    isValid = false;
                }

                // Image is optional in edit, so no validation unless you want it

                if (!isValid) {
                    e.preventDefault();
                }
            });

            form.find('input[name="name"], input[name="image"]').on('input change', function () {
                $(this).next('.text-danger').remove();
            });
        });
    });
</script>



@endsection
