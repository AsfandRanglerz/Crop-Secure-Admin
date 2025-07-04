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
                            <div class="card-body table-striped table-bordered table-responsive">
                                <table class="table" id="table_id_events">
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
                                                    {!! \Illuminate\Support\Str::words(strip_tags($data->description), 20, '...') !!}
                                                    <a href="#" data-toggle="modal" data-target="#privacyModal">Read
                                                        more</a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            @if (Auth::guard('admin')->check())
                                                <td><a href="{{ url('/admin/privacy-policy-edit') }}"><i
                                                            class="fas fa-edit"></i></a></td>
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
    <!-- Privacy Policy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" role="dialog" aria-labelledby="privacyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {!! $data->description !!}
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')
    <!-- DataTables -->
    <script>
        $(document).ready(function() {
            $('#table_id_events').DataTable({
                paging: true,
                info: false
            });
        });
    </script>
@endsection
