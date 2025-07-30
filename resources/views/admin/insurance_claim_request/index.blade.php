@extends('admin.layout.app')
@section('title', 'Insurance Claim Requests')
@section('content')

    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Insurance Claim Requests</h4>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Farmer Name</th>
                                            <th>Bank Holder</th> <!-- New -->
                                            <th>Bank Name</th> <!-- New -->
                                            <th>Account Number</th> <!-- New -->
                                            <th>Claimed Amount</th>
                                            <th>Total Compensation</th>
                                            <th>Remaining Amount</th>
                                            <th>Insurance Type</th>
                                            <th>Company</th>
                                            <th>Claim Date</th>
                                            <th>Status</th>
                                            {{-- <th>Rejection Reason</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($insuranceClaims as $key => $claim)
                                            @php
                                                $bankDetail = $claim->userBankDetail;
                                            @endphp
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $claim->farmer_name ?? '-' }}</td>
                                                <td>{{ $bankDetail->bank_holder_name ?? '-' }}</td>
                                                <td>{{ $bankDetail->bank_name ?? '-' }}</td>
                                                <td>{{ $bankDetail->account_number ?? '-' }}</td>
                                                <td>Rs. {{ number_format($claim->claimed_amount, 2) }}</td>
                                                <td>Rs. {{ number_format($claim->compensation_amount, 2) }}</td>
                                                <td>Rs. {{ number_format($claim->remaining_amount, 2) }}</td>
                                                <td>{{ $claim->insuranceType->name ?? '-' }}</td>
                                                <td>{{ $claim->company ?? '-' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($claim->claimed_at)->format('d M Y') }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <div class="dropdown mr-2">
                                                            <button
                                                                class="btn btn-sm
                                                                    @if ($claim->status === 'approved') btn-success 
                                                                    @elseif($claim->status === 'rejected') btn-danger 
                                                                    @else btn-warning @endif
                                                                    @if ($claim->status !== 'approved') dropdown-toggle @endif"
                                                                type="button"
                                                                @if ($claim->status !== 'approved') data-toggle="dropdown" @else disabled @endif>
                                                                {{ ucfirst($claim->status) }}
                                                            </button>

                                                            @if ($claim->status !== 'approved')
                                                                <div class="dropdown-menu">
                                                                    @if ($claim->status === 'pending')
                                                                        <a class="dropdown-item approve-btn" href="#"
                                                                            data-id="{{ $claim->id }}"
                                                                            data-toggle="modal"
                                                                            data-target="#uploadModal{{ $claim->id }}">
                                                                            Approve
                                                                        </a>

                                                                        <form method="POST"
                                                                            action="{{ route('insurance.claim.reject', $claim->id) }}">
                                                                            @csrf
                                                                            <button type="submit"
                                                                                class="dropdown-item text-danger">Reject</button>
                                                                        </form>
                                                                    @elseif ($claim->status === 'rejected')
                                                                        <a class="dropdown-item approve-btn" href="#"
                                                                            data-id="{{ $claim->id }}"
                                                                            data-toggle="modal"
                                                                            data-target="#uploadModal{{ $claim->id }}">
                                                                            Approve
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            @endif

                                                        </div>

                                                        @if ($claim->bill_image)
                                                            <a href="{{ asset('public/' . $claim->bill_image) }}"
                                                                target="_blank" class="btn btn-sm btn-info"
                                                                title="View Uploaded Bill">
                                                                <i class="fa fa-paperclip"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>


                                                {{-- <td>
                                                    @if (!empty($claim->rejection_reason))
                                                        {{ $claim->rejection_reason }}
                                                    @else
                                                        No Rejection
                                                    @endif
                                                </td> --}}


                                                {{-- <td>
                                                    @if (Auth::guard('admin')->check() || $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Types & Sub-Types' && $permission['permissions']->contains('delete')))
                                                        <form action="{{ route('insurance.claim.destroy', $claim->id) }}"
                                                            method="POST" style="display:inline-block; margin-left: 10px">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-danger btn-flat show_confirm"
                                                                data-toggle="tooltip">Delete</button>
                                                        </form>
                                                    @endif
                                                </td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Upload Bill Modal --}}
    @foreach ($insuranceClaims as $claim)
        <div class="modal fade" id="uploadModal{{ $claim->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form action="{{ route('insurance.claim.approve', $claim->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Upload Bill Screenshot</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <label>Upload Image:</label>
                            <input type="file" name="bill_image" accept="image/*" class="form-control" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Submit & Approve</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Bank Details Modal --}}
    <div class="modal fade" id="bankModalUniversal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bank Details</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p><strong>Holder Name:</strong> <span id="bankHolder"></span></p>
                    <p><strong>Bank Name:</strong> <span id="bankName"></span></p>
                    <p><strong>Account Number:</strong> <span id="bankAccount"></span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    {{-- @foreach ($insuranceClaims as $claim)
        <div class="modal fade" id="rejectModal{{ $claim->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form action="{{ route('insurance.claim.reject', $claim->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Reject Claim</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="description{{ $claim->id }}">Description (Reason for Rejection)</label>
                                <textarea name="description" id="description{{ $claim->id }}" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Submit & Reject</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach --}}

@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#table_id_events').DataTable({
                paging: true,
                info: false,
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Generate Excel Report',
                    exportOptions: {
                        columns: ':not(.noExport)' // Is class wali columns export nahi hongi
                    }
                }]
            });

            $('#bankModalUniversal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                $('#bankHolder').text(button.data('holder'));
                $('#bankName').text(button.data('bank'));
                $('#bankAccount').text(button.data('account'));
            });

            $('.show_confirm').click(function(event) {
                event.preventDefault();
                var form = $(this).closest("form");

                swal({
                    title: `Are you sure you want to delete this record?`,
                    text: "If you delete this, it will be gone forever.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) form.submit();
                });
            });
        });
    </script>

@endsection
