@extends('admin.layout.app')
@section('title', 'Village Results')

@section('content')
    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">

                {{-- Back Button --}}
                <div class="row mb-2">
                    <div class="col-12">
                        <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">{{ $village->name ?? 'Village Results' }}</h4>
                            </div>

                            <div class="card-body table-striped table-bordered table-responsive">
                                <table class="table responsive" id="table_id_results">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Average Temperature (°C)</th>
                                            <th>Average Rainfall (mm)</th>
                                            <th>Date</th>
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
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">No data found.</td>
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

{{-- DataTables JS --}}
@section('js')
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#table_id_results').DataTable();
        });
    </script>
@endsection
