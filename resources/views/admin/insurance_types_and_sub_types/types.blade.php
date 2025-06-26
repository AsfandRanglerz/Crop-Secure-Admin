@extends('admin.layout.app')
@section('title', 'Insurance Types')
@section('content')

    {{-- Add Insurance Types Modal --}}
    <div class="modal fade" id="InsuranceTypesModal" tabindex="-1" role="dialog"
        aria-labelledby="InsuranceTypesModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="InsuranceTypesModalLabel">Add Insurance Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('insurance.type.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="" {{ old('status') === null ? 'selected' : '' }} disabled>
                                            Select an Option</option>
                                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active
                                        </option>
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
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    {{-- Edit Insurance Types Modal --}}
    @foreach ($InsuranceTypes as $InsuranceType)
        <div class="modal fade" id="EditInsuranceTypesModal-{{ $InsuranceType->id }}" tabindex="-1" role="dialog"
            aria-labelledby="EditInsuranceTypesModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="EditInsuranceTypesModalLabel">Edit Insurance Type</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('insurance.type.update', $InsuranceType->id) }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $InsuranceType->name) }}">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" class="form-control" required>
                                            <option value="" {{ old('status', $InsuranceType->status) === null ? 'selected' : '' }} disabled>Select an Option</option>
                                            <option value="1" {{ old('status', $InsuranceType->status) == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $InsuranceType->status) == '0' ? 'selected' : '' }}>Deactive</option>
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





    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <a class="btn btn-primary mb-2" href="{{ route('insurance.company.index') }}">Back</a>
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Insurance Types</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                @if (Auth::guard('admin')->check() ||
                                        $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Types & Sub-Types' &&
                                                $permission['permissions']->contains('create')))
                                    {{-- <a class="btn btn-primary mb-3 text-white" href="#" data-toggle="modal"
                                    data-target="#InsuranceTypesModal">Create</a> --}}
                                @endif

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Results</th>
                                            <th>Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($InsuranceTypes as $InsuranceType)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $InsuranceType->name }}</td>
                                            @if( $InsuranceType->name == "Area Yield Index")
                                            <td>
                                                <a class="btn btn-primary" href="{{ route('insurance.sub.type.index', $InsuranceType->id) }}">Current Yield</a>
                                            </td>
                                            @elseif( $InsuranceType->name == "Production Price Index")
                                            <td>
                                                <a class="btn btn-primary" href="{{ route('insurance.sub.type.productionPrice', $InsuranceType->id) }}">Average Yield</a>
                                            </td>
                                            @elseif( $InsuranceType->name == "Satellite Index (NDVI)")
                                            <td>
                                                <a class="btn btn-primary" href="{{ route('insurance.sub.type.satelliteNDVI', $InsuranceType->id) }}">NDVI Vegetation</a>
                                            </td>
                                             @elseif( $InsuranceType->name == "Weather Index")
                                            <td>
                                                <a class="btn btn-primary" href="{{ route('insurance.sub.type.weatherIndex', $InsuranceType->id) }}">Results</a>
                                            </td>
                                            @else
                                            <td>--</td>
                                            @endif
                                            <td>
                                                @if ($InsuranceType->status == 1)
                                                <div class="badge badge-success badge-shadow">Activated</div>
                                                @else
                                                    <div class="badge badge-danger badge-shadow">Deactivated</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-4">
                                                    @if (Auth::guard('admin')->check() ||
                                                            $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Types & Sub-Types' &&
                                                                    $permission['permissions']->contains('edit')))
                                                        <a class="btn btn-primary text-white"
                                                            href="#" data-toggle="modal" data-target="#EditInsuranceTypesModal-{{ $InsuranceType->id }}">Edit</a>
                                                    @endif

                                                    <!-- Delete Button -->
                                                    @if (Auth::guard('admin')->check() ||
                                                            $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Types & Sub-Types' &&
                                                                    $permission['permissions']->contains('delete')))
                                                        {{-- <form action="
                                                        {{ route('insurance.type.destroy', $InsuranceType->id) }}
                                                            " method="POST" 
                                                            style="display:inline-block; margin-left: 10px">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-danger btn-flat show_confirm"
                                                                data-toggle="tooltip">Delete</button>
                                                        </form> --}}
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
