@extends('admin.layout.app')
@section('title', 'Weather Index')

@section('content')

    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">{{ $InsuranceType->name }}</h4>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Villages</th>
                                            <th>Results</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($InsuranceSubTypes as $InsuranceSubType)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $InsuranceSubType->name ?? 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.insurance.result', $InsuranceSubType->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> {{-- card-body --}}
                        </div> {{-- card --}}
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#table_id_events').DataTable();
        });
    </script>
@endsection
