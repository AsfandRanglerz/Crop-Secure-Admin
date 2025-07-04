@extends('admin.layout.app')
@section('title', 'Create Farmer')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <a class="btn btn-primary mb-3" href="{{ route('farmers.index') }}">Back</a>
                <form id="add_farmer" action="{{ route('farmer.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card">
                                <h4 class="text-center my-4">Create Farmer</h4>
                                <div class="row mx-0 px-4">
                                    <!-- Name Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                value="{{ old('name') }}">
                                            <div class="invalid-feedback"></div>
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Father Name --}}
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="fname">Father Name (Optional)</label>
                                            <input type="text" class="form-control" id="fname" name="fname"
                                                value="{{ old('fname') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <!-- Email Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="{{ old('email') }}">
                                            <div class="invalid-feedback"></div>
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Password Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group position-relative" style="margin-bottom: 0.5rem;">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" id="password" name="password"
                                                style="padding-right: 2.5rem;">
                                            <span id="togglePasswordIcon" class="fa fa-eye"
                                                style="position: absolute; top: 2.67rem; right: 0.75rem; cursor: pointer;"></span>
                                            <div class="invalid-feedback"></div>
                                            @error('password')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>


                                    <!-- CNIC Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="cnic">CNIC</label>
                                            <input type="text" class="form-control" id="cnic" name="cnic"
                                                value="{{ old('cnic') }}">
                                            <div class="invalid-feedback"></div>
                                            @error('cnic')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Contact Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="contact">Phone</label>
                                            <input type="tel" class="form-control" id="contact" name="contact"
                                                value="{{ old('contact') }}">
                                            <div class="invalid-feedback"></div>
                                            @error('contact')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- DOB Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="dob">DOB (Optional)</label>
                                            <input type="date" class="form-control" id="dob" name="dob"
                                                value="{{ old('dob') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>


                                    <!-- Status Dropdown -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group mb-2">
                                            <label for="status">Status (Optional)</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="" {{ old('status') === null ? 'selected' : '' }}
                                                    disabled>Select an Option</option>
                                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active
                                                </option>
                                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>
                                                    Deactive
                                                </option>
                                            </select>
                                        </div>
                                    </div>


                                    <!-- Image Upload -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="image">Image (Optional)</label>
                                            <input type="file" class="form-control" id="image" name="image">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="card-footer text-center row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary mr-1 btn-bg"
                                            id="submit">Save</button>
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

    <script>
        $(document).ready(function() {

            // Password toggle
            $('#togglePasswordIcon').on('click', function() {
                const $password = $('#password');
                const type = $password.attr('type') === 'password' ? 'text' : 'password';
                $password.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');

            });
            // Hide icon on login click (optional)
            $('.btn-login').on('click', function() {
                $('#togglePasswordIcon').addClass('d-none');
            });

        });
    </script>
@endsection
