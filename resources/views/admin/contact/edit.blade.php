@extends('admin.layout.app')
@section('title', 'Edit Contact Us')
@section('content')

    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <a class="btn btn-primary mb-3" href="{{ url()->previous() }}">Back</a>
                <form id="edit_farmer" action="{{ route('contact.update', $find->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('POST') <!-- Changed to PUT for proper RESTful update -->

                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card">
                                <h4 class="text-center my-4">Edit Contact Us</h4>
                                <div class="row mx-0 px-4">

                                    <!-- Email Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="{{ $find->email }}" placeholder="Enter your email">
                                            <div class="invalid-feedback"></div>
                                            @error('email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Phone Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="phone">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                value="{{ $find->phone }}" placeholder="Enter phone number">
                                            <div class="invalid-feedback"></div>
                                            @error('phone')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>




                                </div>
                                <!-- Submit Button -->

                                <div class="card-footer text-center row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary mr-1 btn-bg" id="submit">Save
                                            Changes</button>
                                    </div>
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
    <script>
        $(document).ready(function() {
            $('#edit_farmer').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Clear previous errors
                        $('.is-invalid').removeClass('is-invalid');
                        $('.invalid-feedback').html('');

                        // Show success message and token
                        toastr.success(response.message);
                        // alert('Generated Token: ' + response.token); // Or use toastr.info

                        // Redirect to index after 2 seconds
                        setTimeout(() => {
                            window.location.href = "{{ route('contact.index') }}";
                        }, 2000);
                    },
                    error: function(xhr) {
                        // Handle validation errors
                        let errors = xhr.responseJSON.errors;
                        for (let field in errors) {
                            $('#' + field).addClass('is-invalid');
                            $('#' + field).next('.invalid-feedback').html(errors[field][0]);
                        }
                    }
                });
            });
        });
    </script>
@endsection
