@extends('admin.layout.app')
@section('title', 'Edit Sub Admin')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <a class="btn btn-primary mb-3" href="{{ route('subadmin.index') }}">Back</a>
                <form id="edit_subadmin" action="{{ route('subadmin.update', $subAdmin->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('POST') <!-- Use PUT method for editing -->
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card">
                                <h4 class="text-center my-4">Edit Sub Admin</h4>
                                <div class="row mx-0 px-4">
                                    <!-- Name Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                value="{{ $subAdmin->name }}">
                                            <div class="invalid-feedback"></div>
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Email Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="text" class="form-control" id="email" name="email"
                                                value="{{ $subAdmin->email }}">
                                            <div class="invalid-feedback"></div>
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- <!-- Password Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" id="password" name="password"
                                                placeholder="Leave blank to keep current password">
                                            <div class="invalid-feedback"></div>
                                            @error('password')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div> --}}


                                    <!-- phone Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="tel">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                value="{{ $subAdmin->phone }}">
                                            <div class="invalid-feedback"></div>
                                            @error('phone')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Status Dropdown -->
                                    {{-- <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group mb-2">
                                            <label for="status">Status</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="" disabled>Select an Option</option>
                                                <option value="1" {{ $subAdmin->status == 1 ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ $subAdmin->status == 0 ? 'selected' : '' }}>Deactive</option>
                                            </select>
                                        </div>
                                    </div> --}}

                                    <!-- Image Upload -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="image">Image (Optional)</label>
                                            <input type="file" class="form-control" id="image" name="image">
                                            <div class="mt-2">
                                                @if ($subAdmin->image)
                                                    <img src="{{ asset($subAdmin->image) }}" alt="Image" width="100">
                                                @endif
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="card-footer text-center row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary mr-1 btn-bg"
                                            id="submit">Update</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@section('js')
    @if (\Illuminate\Support\Facades\Session::has('message'))
        <script>
            toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
        </script>
    @endif
@endsection
