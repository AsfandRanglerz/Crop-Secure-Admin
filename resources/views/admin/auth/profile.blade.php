@extends('admin.layout.app')
@section('title', 'Settings')
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="row mt-sm-4">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="padding-20">
                                <ul class="nav nav-tabs" id="myTab2" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="profile-tab2" data-toggle="tab" href="#settings"
                                            role="tab" aria-selected="true">Settings</a>
                                    </li>
                                </ul>
                                <div class="tab-content tab-bordered" id="myTab3Content">
                                    <div class="tab-pane fade pb-0" id="about" role="tabpanel"
                                        aria-labelledby="home-tab2">
                                        <div class="row">
                                            <div class="col-md-3 col-6 b-r">
                                                <strong>Full Name</strong>
                                                <br>
                                                <p class="text-muted">{{ $data->name }}</p>
                                            </div>
                                            <div class="col-md-3 col-6 b-r">
                                                <strong>Email</strong>
                                                <br>
                                                <p class="text-muted">{{ $data->email }}</p>
                                            </div>
                                            <div class="col-md-3 col-6 b-r">
                                                <strong>Profile Image</strong>
                                                <br>
                                                <div class="mt-2">
                                                    <img alt="image"
                                                        src="{{ Auth::guard('admin')->check() ? asset(Auth::guard('admin')->user()->image) : (Auth::guard('subadmin')->check() ? asset(Auth::guard('subadmin')->user()->image) : asset('path/to/default/image.png')) }}"
                                                        width="100">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="tab-pane fade active show" id="settings" role="tabpanel"
                                        aria-labelledby="profile-tab2">
                                        <form method="post" action="{{ url('admin/update-profile') }}"
                                            enctype="multipart/form-data">
                                            @csrf
                                            {{--                                        @if (\Illuminate\Support\Facades\Session::has('errors')) --}}
                                            {{--                                            {{dd($errors)}} --}}
                                            {{--                                            @endif --}}
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="form-group col-md-6 col-12">
                                                        <label>Name</label>
                                                        <input type="text" name="name" value="{{ $data->name }}"
                                                            class="form-control">
                                                        @error('name')
                                                            <div class="text-danger">
                                                                Please Fill in the Name
                                                            </div>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group col-md-6 col-12">
                                                        <label>Email</label>
                                                        <input type="email" name="email" value="{{ $data->email }}"
                                                            class="form-control">
                                                        @error('email')
                                                            <div class="text-danger">
                                                                Please Fill in the Email
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-6 col-12">
                                                        <label>Profile Image</label>
                                                        <div class="custom-file">
                                                            <input type="file" name="image" class="custom-file-input"
                                                                id="customFile">
                                                            <label class="custom-file-label" for="customFile">Choose
                                                                file</label>
                                                            <div class="mt-2">
                                                                <img alt="image"
                                                                    src="{{ Auth::guard('admin')->check() ? asset(Auth::guard('admin')->user()->image) : (Auth::guard('subadmin')->check() ? asset(Auth::guard('subadmin')->user()->image) : asset('path/to/default/image.png')) }}"
                                                                    width="100">
                                                            </div>
                                                        </div>

                                                        <div class="invalid-feedback">

                                                        </div>
                                                    </div>
                                                    @if (Auth::guard('subadmin')->check())
                                                        <div class="form-group col-md-6 col-12">
                                                            <label>Phone</label>
                                                            <input type="text" name="phone" value="{{ $data->phone }}"
                                                                class="form-control">
                                                            @error('phone')
                                                                <div class="text-danger">Please Fill in the Phone</div>
                                                            @enderror
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="card-footer text-right">
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection
