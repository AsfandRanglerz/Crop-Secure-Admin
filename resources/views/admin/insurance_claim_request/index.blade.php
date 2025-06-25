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
                                <div class="col-12">
                                    <h4>Insurance Claim Requests</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Farmer Name</th>
                                            <th>Claimed Amount</th>
                                            <th>Total Compensation</th>
                                            <th>Remaining Amount</th>
                                            <th>Insurance Type</th>
                                            <th>Company</th>
                                            <th>Claim Date</th>
                                            <th>Bank Details</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($insuranceClaims as $key => $claim)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $claim->user->name ?? '-' }}</td>
                                                <td>Rs. {{ number_format($claim->claimed_amount, 2) }}</td>
                                                <td>Rs. {{ number_format($claim->compensation_amount, 2) }}</td>
                                                <td>Rs. {{ number_format($claim->remaining_amount, 2) }}</td>
                                                <td>{{ $claim->insuranceType->name ?? '-' }}</td>
                                                <td>{{ $claim->company ?? '-' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($claim->claimed_at)->format('d M Y') }}</td>
                                                <td>
                                                    <button class="btn btn-info view-bank-btn"
                                                        data-holder="{{ $claim->farmer->bankDetail->bank_holder_name ?? '' }}"
                                                        data-name="{{ $claim->user->bankDetail->bank_name ?? '' }}"
                                                        data-account="{{ $claim->user->bankDetail->account_number ?? '' }}"
                                                        data-toggle="modal" data-target="#bankModalUniversal">
                                                        View Bank
                                                    </button>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button
                                                            class="btn btn-sm 
                                                            @if ($claim->status === 'approved') btn-success 
                                                            @elseif($claim->status === 'rejected') btn-danger 
                                                            @else btn-warning @endif 
                                                            dropdown-toggle"
                                                            type="button" data-toggle="dropdown">
                                                            {{ ucfirst($claim->status) }}
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item approve-btn" href="#"
                                                                data-id="{{ $claim->id }}" data-toggle="modal"
                                                                data-target="#uploadModal{{ $claim->id }}">Approve</a>
                                                            <form method="POST"
                                                                action="{{ route('insurance.claim.reject', $claim->id) }}">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="dropdown-item text-danger">Reject</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'Insurance Types & Sub-Types' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form
                                                                action="{{ route('insurance.claim.destroy', $claim->id) }}"
                                                                method="POST"
                                                                style="display:inline-block; margin-left: 10px">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-danger btn-flat show_confirm"
                                                                    data-toggle="tooltip">Delete</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
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
                            <button type="submit" class="btn btn-success">Submit &
                                Approve</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Universal Bank Modal --}}
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
