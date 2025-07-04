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

                                <table class="table responsive" id="product_claims_table">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Farmer</th>
                                            <th>Dealer</th>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Address</th>
                                            <th>Delivery Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($claims as $index => $claim)
                                            @php
                                                $productData = json_decode($claim->products, true);
                                            @endphp

                                            @if (!empty($productData))
                                                @foreach ($productData as $product)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $claim->insurance->user->name ?? '-' }}</td>
                                                        <td>
                                                            <strong>{{ $product['dealer_name'] ?? '-' }}</strong><br>
                                                            <small>
                                                                 {{ $product['dealer_contact'] ?? 'N/A' }}<br>
                                                                 {{ $product['dealer_email'] ?? 'N/A' }}
                                                            </small>
                                                        </td>

                                                        <td>{{ $product['name'] ?? '-' }}</td>
                                                        <td>Rs. {{ number_format($product['price'] ?? 0) }}</td>
                                                        <td>
                                                            {{ $claim->state ? $claim->state . ', ' : '' }}
                                                            {{ $claim->city ? $claim->city . ', ' : '' }}
                                                            {{ $claim->address ?? '-' }}
                                                        </td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button
                                                                    class="btn btn-sm 
                            @if ($claim->delivery_status === 'approved') btn-success 
                            @elseif($claim->delivery_status === 'rejected') btn-danger 
                            @else btn-warning @endif dropdown-toggle"
                                                                    type="button" data-toggle="dropdown">
                                                                    {{ ucfirst($claim->delivery_status) }}
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <form method="POST"
                                                                        action="{{ route('product.claim.approve', $claim->id) }}">
                                                                        @csrf
                                                                        <button type="submit"
                                                                            class="dropdown-item approve-btn">Accept</button>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('product.claim.reject', $claim->id) }}">
                                                                        @csrf
                                                                        <button type="submit"
                                                                            class="dropdown-item text-danger">Reject</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($claim->created_at)->format('d M Y') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
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
                paging: true,
                ordering: true,
                responsive: true
            });
        });
    </script>
@endsection
