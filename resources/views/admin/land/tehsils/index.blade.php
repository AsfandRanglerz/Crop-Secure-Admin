@extends('admin.layout.app')
@section('title', 'Tehsils')
@section('content')


    {{-- Add Tehsil Modal --}}
    <div class="modal fade" id="tehsilModal" tabindex="-1" role="dialog" aria-labelledby="tehsilModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tehsilModalLabel">Create Tehsil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="CreateTehsiltForm" action="{{ route('tehsil.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="district_id" value="{{ $district->id }}">
                    <div class="modal-body">
                        <!-- Message Textbox -->
                        <div class="form-group">
                            <label for="message">Tehsil Name</label>
                            <input type="text" name="name" class="form-control">
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



    {{-- Edit Tehsil Modal --}}
    @foreach ($tehsils as $tehsil)
    <div class="modal fade" id="edittehsilModal-{{$tehsil->id}}" tabindex="-1" role="dialog" aria-labelledby="edittehsilModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edittehsilModalLabel">Edit Tehsil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="EditTehsilForm" action="{{ route('tehsil.update', $tehsil->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="district_id" value="{{ $district->id }}">
                    <div class="modal-body">
                        <!-- Message Textbox -->
                        <div class="form-group">
                            <label for="message">Tehsil Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $tehsil->name }}">
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
                        <a class="btn btn-primary mb-2" href="{{ route('land.index') }}">Back</a>
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>{{$district->name}} - Tehsils</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                <a class="btn btn-primary mb-3" href="#" data-toggle="modal"
                                                    data-target="#tehsilModal">Create Tehsil</a>
                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Union Council</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tehsils as $tehsil)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $tehsil->name }}</td>
                                                <td>
                                                    <a class="btn btn-primary" href="{{ route('union.index', $tehsil->id) }}">Union Council</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        <a href="#" data-toggle="modal" data-target="#edittehsilModal-{{$tehsil->id}}"
                                                            class="btn btn-primary" style="margin-left: 10px">Edit</a>
                                                        <form
                                                            action="
                                                    {{ route('tehsil.destroy', $tehsil->id) }}
                                                     "
                                                            method="POST"
                                                            style="display:inline-block; margin-left: 10px">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="district_id" value="{{ $district->id }}">
                                                            <button type="submit"
                                                                class="btn btn-danger btn-flat show_confirm"
                                                                data-toggle="tooltip">Delete</button>
                                                        </form>
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

    @if ($errors->has('name'))
        <script>
            toastr.error("{{ $errors->first('name') }}", 'Validation Error', {
                timeOut: 5000
            });
        </script>
    @endif

    <script>
        $(document).ready(function() {
            // ✅ Form validation for Create District
            $('#CreateTehsiltForm').on('submit', function(e) {
                let form = $(this);
                let isValid = true;

                const nameField = form.find('input[name="name"]');
                const name = nameField.val().trim();

                // Clear old error
                form.find('.text-danger').remove();

                if (name === '') {
                    nameField.after('<span class="text-danger">The Tehsil name is required.</span>');
                    isValid = false;
                }

                if (!isValid) e.preventDefault();
            });

            // ✅ Remove error on input
            $('#CreateDistrictForm input[name="name"]').on('input', function() {
                $(this).next('.text-danger').remove();
            });
        });

         $(document).ready(function() {
            $('.EditTehsilForm').each(function() {
                const form = $(this);

                form.on('submit', function(e) {
                    let isValid = true;

                    const nameField = form.find('input[name="name"]');
                    const name = nameField.val().trim();

                    // Clear old errors
                    form.find('.text-danger').remove();

                    if (name === '') {
                        nameField.after(
                            '<span class="text-danger">The Tehsil name is required.</span>');
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
