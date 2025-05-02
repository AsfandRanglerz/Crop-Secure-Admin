@extends('admin.layout.app')
@section('title', 'Insured Crops')
@section('content')


    {{-- Add Ensured Crops Modal --}}
    <div class="modal fade" id="EnsuredCropModal" tabindex="-1" role="dialog" aria-labelledby="EnsuredCropModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="EnsuredCropModalLabel">Add Insured Crop</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('ensured.crop.name.store') }}" method="POST">
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
                                        <input type="number" name="sum" class="form-control" value="{{ old('sum') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text font-weight-bold" style="border: 2px solid #cbd2d8;">PKR</span>
                                        </div>
                                    </div>
                                    @error('sum')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>                            
                                <small class="text-muted ml-3">(Note:The sum insured value applied to 100% benchmark against 1 acre)</small>
                            </div> 
                           
                    
                            <div class="form-group">
                                <label>Harvest Time Period</label>
                                <div class="d-flex">
                                    <!-- Harvest Start Month Dropdown -->
                                    <select name="harvest_start" class="form-control">
                                        <option value="" disabled selected>Start Month</option>
                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                            
                                    <span class="mx-2"></span>
                            
                                    <!-- Harvest End Month Dropdown -->
                                    <select name="harvest_end" class="form-control">
                                        <option value="" disabled selected>End Month</option>
                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            

                            <div class="form-group">
                                <label>Insurance Purchase Period</label>
                                <div class="d-flex">
                                    <!-- Insurance Start Month Dropdown -->
                                    <select name="insurance_start" class="form-control">
                                        <option value="" disabled selected>Start Month</option>
                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                            
                                    <span class="mx-2"></span>
                            
                                    <!-- Insurance End Month Dropdown -->
                                    <select name="insurance_end" class="form-control">
                                        <option value="" disabled selected>End Month</option>
                                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
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



    {{-- Edit Ensured Crops Modal --}}
    @foreach ($EnsuredCropNames as $EnsuredCrop)
        <div class="modal fade" id="EditEnsuredCropModal-{{ $EnsuredCrop->id }}" tabindex="-1" role="dialog"
            aria-labelledby="EditEnsuredCropModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
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
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $EnsuredCrop->name) }}">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label>Sum Insured</label>
                                        <input type="number" name="sum" class="form-control" value="{{ old('sum', $EnsuredCrop->sum_insured_value) }}">
                                        @error('sum')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        <div class="form-group">
                            <label>Harvest Time Period</label>
                            <div class="d-flex">
                                <select name="harvest_start" class="form-control">
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month }}" {{ old('harvest_start', $EnsuredCrop->harvest_start_time) == $month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                                <span class="mx-2"></span>
                                <select name="harvest_end" class="form-control">
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month }}"  {{ old('harvest_end', $EnsuredCrop->harvest_end_time) == $month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Insurance Purchase Period</label>
                            <div class="d-flex">
                                <select name="insurance_start" class="form-control">
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month }}"  {{ old('insurance_start', $EnsuredCrop->insurance_start_time) == $month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                                <span class="mx-2"></span>
                                <select name="insurance_end" class="form-control">
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month  }}"  {{ old('insurance_end', $EnsuredCrop->insurance_end_time) == $month ? 'selected' : '' }}>{{ $month }}</option>
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
                        <a class="btn btn-primary mb-2" href="{{ route('farmers.index') }}">Back</a>
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Insured Crops</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                @if (Auth::guard('admin')->check() ||
                                        $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Ensured Crops' &&
                                                $permission['permissions']->contains('create')))
                                    <a class="btn btn-primary mb-3 text-white"
                                        href="#" data-toggle="modal" data-target="#EnsuredCropModal">Create</a>
                                @endif

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Sum Insured</th>
                                            <th>Harvest Start</th>
                                            <th>Harvest End</th>
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
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Ensured Crops' &&
                                                                        $permission['permissions']->contains('edit')))
                                                            <a href="#"
                                                                class="btn btn-primary" data-toggle="modal" data-target="#EditEnsuredCropModal-{{ $EnsuredCrop->id }}">Edit</a>
                                                        @endif

                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Ensured Crops' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form action="
                                                            {{ route('ensured.crop.name.destroy', $EnsuredCrop->id) }}
                                                            " method="POST" 
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

@endsection
