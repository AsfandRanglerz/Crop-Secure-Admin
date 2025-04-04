@extends('admin.layout.app')
@section('title', 'Privacy Policy')
@section('content')
    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Privacy Policy</h4>
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
                                            @if ($data)
                                            
                                            {!! $data->description !!}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        @if (Auth::guard('admin')->check())
                                            
                                        <td><a href="{{url('/admin/privacy-policy-edit')}}"><i class="fas fa-edit"></i></a></td>
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
    
@endsection
