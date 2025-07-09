@extends('admin.layout.app')
@section('title', 'Create FAQ')
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <form action="{{ route('faq.store') }}" method="POST">
                    @csrf
                    <a href="{{ route('faqs') }}" class="btn mb-3" style="background: #009245;">Back</a>
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Create FAQ</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="question">Question</label>
                                        <input type="text" name="question" class="form-control" id="question">
                                        <div class="invalid-feedback"></div>
                                        @error('question')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="answer">Answer</label>
                                        <textarea name="answer" class="form-control" id="answer"></textarea>
                                        <div class="invalid-feedback"></div>
                                        @error('answer')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary mr-1">Save</button>
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
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('answer');
    </script>
@endsection
