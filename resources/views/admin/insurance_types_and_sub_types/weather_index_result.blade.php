@extends('admin.layout.app')
@section('title', 'Village Results')
@section('content')

    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <a class="btn btn-primary mb-3" href="{{ url()->previous() }}">Back</a>
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0">{{ $village->name ?? 'Village Results' }}</h4>
                                    @if ($cropData)
                                        <small
                                            style="font-size: 14px; color: #333; display: inline-block; margin-top: 4px;">
                                            Average Temperature: <strong>{{ $cropData->avg_temp ?? 'N/A' }}°C</strong>,
                                            Average Rainfall: <strong>{{ $cropData->avg_rainfall ?? 'N/A' }} mm</strong>
                                        </small>
                                    @endif
                                </div>
                            </div>



                            <div class="card-body table-striped table-bordered table-responsive">
                                <table class="table" id="table_id_results">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Recorded Temperature (°C)</th>
                                            <th>Recorded Rainfall (mm)</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            {{-- <th>Loss Flag</th>
                                            <th>Loss Reason</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($villageWeathers as $index => $villageCrop)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $villageCrop->temperature ?? 'N/A' }}</td>
                                                <td>{{ $villageCrop->rainfall ?? 'N/A' }}</td>
                                                <td>{{ $villageCrop->date ? \Carbon\Carbon::parse($villageCrop->date)->format('d M Y') : 'N/A' }}
                                                </td>
                                                <td>{{ $villageCrop->time ?? 'N/A' }}</td>
                                                {{-- <td>
                                                    @if ($villageCrop->loss_flag)
                                                        <span class="badge badge-danger">Yes</span>
                                                    @else
                                                        <span class="badge badge-success">No</span>
                                                    @endif
                                                </td>
                                                <td>{{ $villageCrop->loss_reason ?? '-' }}</td> --}}
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No data found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div> {{-- card-body --}}
                        </div> {{-- card --}}
                    </div> {{-- col --}}
                </div> {{-- row --}}
            </div> {{-- section-body --}}
        </section>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#table_id_results').DataTable({
                responsive: true,
                paging: true,
                ordering: true,
                searching: true
            });
        });
    </script>
@endsection
