@extends('admin.layout.app')
@section('title', 'Contact Us')
@section('content')

    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Contact Us</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                {{-- @if (Auth::guard('admin')->check() || $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'ContactUs' && $permission['permissions']->contains('create')))
                                    <a class="btn btn-primary mb-3 text-white"
                                        href="{{ route('interest.createview') }}">Create</a>
                                @endif --}}

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Email</th>
                                            <th>Phone</th>



                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($contacts as $contact)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>



                                                <td>{{ $contact->email }}</td>
                                                <td>{{ $contact->phone }}</td>


                                                <td>
                                                    <div class="d-flex gap-4">


                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'ContactUs' &&
                                                                        $permission['permissions']->contains('edit')))
                                                            <a href="{{ route('contact.updateview', $contact->id) }}"
                                                                class="btn btn-primary me-2"
                                                                style="float: left; margin-right: 8px;">Edit</a>
                                                        @endif

                                                </td>
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
            var formId = $(this).data("form");
            var form = document.getElementById(formId);
            event.preventDefault();
            swal({
                    title: "Are you sure?",
                    text: "This action cannot be undone!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        // Send AJAX request
                        $.ajax({
                            url: form.action,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                // सफलता टोस्ट दिखाएं और 3 सेकंड बाद पेज रिफ्रेश करें
                                swal({
                                    title: "Success!",
                                    text: "Record deleted successfully!",
                                    icon: "success",
                                    button: false,
                                    timer: 3000
                                }).then(() => {
                                    location.reload(); // टोस्ट बंद होने पर पेज रिफ्रेश
                                });
                            },
                            error: function(xhr) {
                                swal("Error!", "Failed to delete record.", "error");
                            }
                        });
                    }
                });
        });
    </script>

@endsection
