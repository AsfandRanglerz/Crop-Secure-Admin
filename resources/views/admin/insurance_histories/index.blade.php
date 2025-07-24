@extends('admin.layout.app')
@section('title', 'Purchased Insurances')
@section('content')

    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">

                            <!-- Header with Total & Filters -->
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <h4 class="mb-2 mb-md-0">Purchased Insurances</h4>
                                    <div class="mb-2 mb-md-0 font-weight-bold">
                                        - Total Amount: {{ number_format($totalPayableAmount) }} PKR
                                    </div>
                                </div>

                                <form method="GET" action="{{ url()->current() }}"
                                    class="form-inline d-flex flex-wrap gap-2 mt-2" id="filterForm">
                                    <select name="year" class="form-control rounded-0 mr-2 mb-2 mb-md-0 auto-submit">
                                        <option value="">Year</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}"
                                                {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="insurance_type"
                                        class="form-control rounded-0 mr-2 mb-2 mb-md-0 auto-submit">
                                        <option value="">Insurance Type</option>
                                        @foreach ($insuranceTypes as $type)
                                            <option value="{{ $type }}"
                                                {{ request('insurance_type') == $type ? 'selected' : '' }}>
                                                {{ $type }}</option>
                                        @endforeach
                                    </select>

                                    <select name="company" class="form-control rounded-0 mr-2 mb-2 mb-md-0 auto-submit">
                                        <option value="">Company</option>
                                        @foreach ($companies as $comp)
                                            <option value="{{ $comp }}"
                                                {{ request('company') == $comp ? 'selected' : '' }}>{{ $comp }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            <!-- Table -->
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
                                            <th class="noExport">Land Image</th>
                                            <th class="noExport">Certificate</th>
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
                                            <th>Date</th>
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
                                                <td>
                                                    @if ($history->land_data && $history->land_data->image)
                                                        <a href="javascript:void(0)" data-toggle="modal"
                                                            data-target="#imageModal"
                                                            data-image="{{ asset($history->land_data->image) }}"
                                                            title="View Land Image">
                                                            <img src="{{ asset($history->land_data->image) }}"
                                                                alt="Land Image" width="70" height="70"
                                                                style="object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                                                        </a>
                                                    @else
                                                        <span class="text-muted">No Land Image</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if ($history->land_data && $history->land_data->certificate)
                                                        <a href="{{ asset($history->land_data->certificate) }}"
                                                            target="_blank" class="btn btn-sm btn-info"
                                                            title="View Certificate">
                                                            <i class="fa fa-paperclip"></i>
                                                        </a>
                                                    @else
                                                        <span class="text-muted">No Certificate</span>
                                                    @endif
                                                </td>


                                                <td>{{ $history->insurance_type ?? '-' }}</td>
                                                <td>{{ $history->district->name ?? '-' }}</td>
                                                <td>{{ $history->tehsil->name ?? '-' }}</td>
                                                <td>{{ $history->company ?? '-' }}</td>
                                                <td>{{ $history->premium_price ? number_format($history->premium_price) . ' PKR' : '-' }}
                                                </td>
                                                <td>{{ $history->sum_insured ? number_format($history->sum_insured) . ' PKR' : '-' }}
                                                </td>
                                                <td>{{ $history->payable_amount ? number_format($history->payable_amount) . ' PKR' : '-' }}
                                                </td>
                                                <td>{{ $history->benchmark ? $history->benchmark . '%' : '-' }}</td>
                                                <td>{{ $history->benchmark_price ? number_format($history->benchmark_price) . ' PKR' : '-' }}
                                                </td>
                                                <td>{{ $history->receipt_number ?? '-' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($history->created_at)->format('d-M-Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="19">No insurance history found.</td>
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

    <!-- Image Modal -->
    <div id="imageModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-transparent shadow-none">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="text-white">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-transparent">
                    <img id="modalImage" src="" alt="" style="width: 100%;">
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script type="text/javascript">
        $('.show_confirm').click(function(event) {
            var form = $(this).closest("form");
            event.preventDefault();
            swal({
                title: `Are you sure you want to delete this record?`,
                text: "If you delete this, it will be gone forever.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    form.submit();
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#imageModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var imageUrl = button.data('image');
                var modal = $(this);
                modal.find('#modalImage').attr('src', imageUrl);
            });

            $('.auto-submit').on('change', function() {
                $('#filterForm').submit();
            });

            var table = $('#table_id_events').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Generate Excel Report', // 👈 Custom button text

                        exportOptions: {
                            columns: ':not(.noExport)' // Exclude columns with 'noExport' class
                        }
                    },
                    //    {
                    // extend: 'print',
                    // exportOptions: {
                    //     columns: ':not(.noExport)'
                    // },
                    // className: 'custom-red-btn',
                    // text: 'PDF' // Renamed button label
                    // }
                ],
                scrollX: true,
                responsive: true
            });
        });
    </script>
@endsection
