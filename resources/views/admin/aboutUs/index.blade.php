@extends('admin.layout.app')
@section('title', 'About Us')
@section('content')
    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>About Us</h4>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Description</th>
                                        @if (Auth::guard('admin')->check())
                                            
                                        <th scope="col">Action</th>
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <th scope="row">1</th>
                                        <td>
                                            {{-- {!! $data->description !!} --}}
                                            About Us is here
                                        </td>
                                        @if (Auth::guard('admin')->check())
                                            
                                        <td><a href="{{url('/admin/about-us-edit')}}"><i class="fas fa-edit"></i></a></td>
                                        @endif
                                    </tr>

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
    @if(\Illuminate\Support\Facades\Session::has('message'))
        <script>
            toastr.success('{{\Illuminate\Support\Facades\Session::get('message')}}');
        </script>
    @endif
@endsection
