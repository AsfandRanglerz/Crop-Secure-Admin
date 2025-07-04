@extends('admin.layout.app')
@section('title', 'Authorized Dealers')
@section('content')


    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Authorized Dealers</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">

                                @if (Auth::guard('admin')->check() || $sideMenuPermissions->contains(fn ($permission) => $permission['side_menu_name'] === 'Authorized Dealers' && $permission['permissions']->contains('create')))
                                    <a class="btn btn-primary mb-3 text-white" href="{{ route('dealer.create') }}">Create</a>
                                @endif


                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Father Name</th>
                                            <th>Email</th>
                                            <th>Image</th>
                                            <th>CNIC</th>
                                            <th>DOB</th>
                                            <th>Phone</th>
                                            <th>District</th>
                                            <th>Items</th>
                                            {{-- <th>Status</th> --}}
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dealers as $dealer)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $dealer->name }}</td>
                                                <td>{{ $dealer->father_name }}</td>
                                                <td><a href="mailto:{{ $dealer->email }}">{{ $dealer->email }}</a></td>
                                                <td>
                                                    <img src="{{ asset($dealer->image) }}" alt=""
                                                        height="50"width="50" class="image">
                                                </td>
                                                <td>{{ $dealer->cnic }}</td>
                                                <td>{{ \Carbon\Carbon::parse($dealer->dob)->format('d/m/Y') }}</td>
                                                <td>{{ $dealer->contact }}</td>
                                                <td>{{ $dealer->district->name ?? 'N/A' }}</td>
                                                <td>
                                                    <a href="
                                                    {{ route('dealer.item.index', $dealer->id) }}
                                                     " class="btn btn-primary">View</a>
                                                </td>
                                                {{-- <td>
                                                    @if ($dealer->status == 1)
                                                    <div class="badge badge-success badge-shadow">Activated</div>
                                                    @else
                                                    <div class="badge badge-danger badge-shadow">Deactivated</div>
                                                @endif
                                                </td> --}}
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        @if (Auth::guard('admin')->check() || ($sideMenuPermissions->contains(fn ($permission) => $permission['side_menu_name'] === 'Authorized Dealers' && $permission['permissions']->contains('edit'))))
                                                        <div class="d-flex gap-4">
                                                            <a href="{{ route('dealer.edit', $dealer->id) }}" class="btn btn-primary">Edit</a>
                                                        </div>
                                                    @endif                                                    
                                                    @if (Auth::guard('admin')->check() || $sideMenuPermissions->contains(fn ($permission) => $permission['side_menu_name'] === 'Authorized Dealers' && $permission['permissions']->contains('delete')))
                                                    <form action="{{ route('dealer.destroy', $dealer->id) }}" method="POST" style="display:inline-block; margin-left: 10px">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-flat show_confirm" data-toggle="tooltip">Delete</button>
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

@endsection
