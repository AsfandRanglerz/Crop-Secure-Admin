@extends('admin.layout.app')
@section('title', 'Edit Dealer')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <a class="btn btn-primary mb-3" href="{{ url()->previous() }}">Back</a>
                <form id="edit_dealer" action="{{ route('dealer.update', $dealer->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST') <!-- Use PUT method for editing -->
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card">
                                <h4 class="text-center my-4">Edit Dealer</h4>
                                <div class="row mx-0 px-4">
                                    <!-- Name Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                            value="{{ $dealer->name }}"
                                             required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <!-- Father Name Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="father_name">Father Name</label>
                                            <input type="text" class="form-control" id="father_name" name="father_name" 
                                            value="{{ $dealer->father_name }}"
                                             required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <!-- Email Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="text" class="form-control" id="email" name="email" 
                                            value="{{ $dealer->email }}"
                                             required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <!-- CNIC Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="cnic">CNIC</label>
                                            <input type="text" class="form-control" id="cnic" name="cnic" value="{{ $dealer->cnic }}"
                                                required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <!-- DOB Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="dob">DOB</label>
                                            <input type="date" class="form-control" id="dob" name="dob" value="{{ $dealer->dob ? $dealer->dob->format('Y-m-d') : '' }}"
                                                required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <!-- Contact Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="contact">Contact</label>
                                            <input type="tel" class="form-control" id="contact" name="contact" value="{{ $dealer->contact }}"
                                                required>
                                            <div class="invalid-feedback"></div>
                                            @error('contact')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- District Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="district">District</label>
                                            <input type="text" class="form-control" id="district" name="district" value="{{ $dealer->district }}"
                                                required>
                                            <div class="invalid-feedback"></div>
                                            @error('district')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Status Dropdown -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group mb-2">
                                            <label for="status">Status</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="" disabled>Select an Option</option>
                                                <option value="1" 
                                                {{ $dealer->status == 1 ? 'selected' : '' }}
                                                >Active</option>
                                                <option value="0" 
                                                {{ $dealer->status == 0 ? 'selected' : '' }}
                                                >Deactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Image Upload -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="image">Image</label>
                                            <input type="file" class="form-control" id="image" name="image">
                                            <div class="mt-2">
                                                @if ($dealer->image)
                                                    <img src="{{ asset( $dealer->image) }}" alt="Image" width="100">
                                                @endif
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="card-footer text-center row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary mr-1 btn-bg" id="submit">Update</button>
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
