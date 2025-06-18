@extends('admin.layout.app')
@section('title', 'Insurance History')
@section('content')


    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>Insurance History</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Crop</th>
                                            <th>Area Unit</th>
                                            <th>Area (acre)</th>
                                            <th>Land Area</th>
                                            <th>Insurance Type</th>
                                            <th>District</th>
                                            <th>Tehsil</th>
                                            <th>Company</th>
                                            <th>Premium Price</th>
                                            <th>Sum Insured</th>
                                            <th>Payable Amount</th>
                                            <th>Benchmark</th>
                                            <th>Benchmark Price</th>
                                            <th>Receipt Number</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($histories as $index => $history)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $history->farmer_name ?? '-' }}</td>
                                                <td>{{ $history->crop ?? '-' }}</td>
                                                <td>{{ $history->area_unit ?? '-' }}</td>
                                                <td>{{ $history->area ?? '-' }}</td>
                                                <td>{{ $history->land ?? '-' }}</td>
                                                <td>{{ $history->insurance_type ?? '-' }}</td>
                                                <td>{{ $history->district ?? '-' }}</td>
                                                <td>{{ $history->tehsil ?? '-' }}</td>
                                                <td>{{ $history->company ?? '-' }}</td>
                                                <td>{{ $history->premium_price ?? '-' }}</td>
                                                <td>{{ $history->sum_insured ?? '-' }}</td>
                                                <td>{{ $history->payable_amount ?? '-' }}</td>
                                                <td>{{ $history->benchmark ?? '-' }}</td>
                                                <td>{{ $history->benchmark_price ?? '-' }}</td>
                                                <td>{{ $history->receipt_number ?? '-' }}</td>
                                                <td>
                                                    <!-- Actions like delete/edit if needed -->
                                                    <form action="{{ route('insurance-history.destroy', $history->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-danger show_confirm">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="17">No insurance history found.</td>
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
            $('#table_id_events').DataTable()
        })
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script type="text/javascript">
        $('.show_confirm').click(function(event) {
            var form = $(this).closest("form");
            var name = $(this).data("name");
            event.preventDefault();
            swal({
                    title: `Are you sure you want to delete this record?`,
                    text: "If you delete this, it will be gone forever.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        form.submit();
                    }
                });
        });
    </script>

@endsection
