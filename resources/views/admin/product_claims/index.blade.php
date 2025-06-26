@extends('admin.layout.app')
@section('title', 'Claim Product Purchases')
@section('content')

<div class="main-content" style="min-height: 562px;">
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="col-12">
                                <h4>Claim Product Purchases</h4>
                            </div>
                        </div>
                        <div class="card-body table-striped table-bordered table-responsive">

                            <table class="table table-bordered" id="product_claims_table">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Farmer</th>
                                        <th>Dealer</th>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Receiver</th>
                                        <th>Address</th>
                                        <th>Delivery Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($claims as $index => $claim)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $claim->insurance->user->name ?? '-' }}</td>
                                            <td>{{ $claim->dealer->name ?? '-' }}</td>
                                            <td>{{ $claim->item->name ?? '-' }}</td>
                                            <td>Rs. {{ number_format($claim->price) }}</td>
                                            <td>{{ $claim->receiver_name ?? '-' }}</td>
                                            <td>
                                                {{ $claim->state ? $claim->state . ', ' : '' }}
                                                {{ $claim->city ? $claim->city . ', ' : '' }}
                                                {{ $claim->address ?? '-' }}
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    {{ $claim->delivery_status == 'pending' ? 'bg-warning' : 
                                                        ($claim->delivery_status == 'delivered' ? 'bg-success' : 'bg-secondary') }}">
                                                    {{ ucfirst($claim->delivery_status) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($claim->created_at)->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            {{ $claims->links() }}

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
        $('#product_claims_table').DataTable({
            paging: false,
            ordering: true,
            responsive: true
        });
    });
</script>
@endsection
