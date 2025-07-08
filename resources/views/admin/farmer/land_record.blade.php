@extends('admin.layout.app')
@section('title', 'Land Record')

@section('content')
    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <a class="btn btn-primary mb-3" href="{{ route('farmers.index') }}">Back</a>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Land Record of {{ $farmer->name }}</h4>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                <table class="table responsive" id="land_record_table">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>District</th>
                                            <th>Tehsil</th>
                                            <th>UC</th>
                                            <th>Village</th>
                                            <th>More Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($farmer->cropInsurances as $record)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $record->district->name ?? 'N/A' }}</td>
                                                <td>{{ $record->tehsil->name ?? 'N/A' }}</td>
                                                <td>{{ $record->uc ?? 'N/A' }}</td>
                                                <td>{{ $record->village ?? 'N/A' }}</td>
                                                <td>{{ $record->other ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">No land record found.</td>
                                            </tr>
                                        @endforelse
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
    <script>
        $(document).ready(function() {
            $('#land_record_table').DataTable();
        });
    </script>
@endsection
