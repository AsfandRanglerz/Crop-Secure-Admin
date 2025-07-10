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
                                                $totalPrice = 0;
                                            @endphp

                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $claim->insurance->user->name ?? '-' }}</td>
                                                <td>
                                                    @php
                                                        $dealerDetails = [];
                                                        foreach ($productData as $product) {
                                                            $dealer = isset($product['dealer_id'])
                                                                ? \App\Models\AuthorizedDealer::find(
                                                                    $product['dealer_id'],
                                                                )
                                                                : null;
                                                            $dealerDetails[] = [
                                                                'name' =>
                                                                    $dealer->dealer_name ??
                                                                    ($product['dealer_name'] ?? '-'),
                                                                'contact' =>
                                                                    $dealer->contact ??
                                                                    ($product['dealer_contact'] ?? 'N/A'),
                                                                'email' =>
                                                                    $dealer->email ??
                                                                    ($product['dealer_email'] ?? 'N/A'),
                                                            ];
                                                        }

                                                        // remove duplicates
                                                        $uniqueDealers = collect($dealerDetails)->unique('name');
                                                    @endphp

                                                    <ul class="mb-0 pl-3">
                                                        @foreach ($uniqueDealers as $dealer)
                                                            <li>
                                                                <strong>{{ $dealer['name'] }}</strong><br>
                                                                <small>{{ $dealer['contact'] }}<br>
                                                                    <a
                                                                        href="mailto:{{ $dealer['email'] }}">{{ $dealer['email'] }}</a></small>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                                {{-- Product Info --}}
                                                <td>
                                                    <ul class="mb-0 pl-3">
                                                        @foreach ($productData as $product)
                                                            @php
                                                                $qty = $product['quantity'] ?? 1;
                                                                $price = $product['price'] ?? 0;
                                                                $totalPrice += $qty * $price;
                                                            @endphp
                                                            <li>{{ $product['name'] ?? '-' }} (Qty: {{ $qty }}, Rs.
                                                                {{ number_format($price) }})</li>
                                                        @endforeach
                                                    </ul>
                                                </td>

                                                {{-- Total Price --}}
                                                <td><strong>{{ number_format($totalPrice) }}</strong> PKR</td>

                                                {{-- Address --}}
                                                <td>
                                                    {{ $claim->insurance->user->claimAddress->state ?? '-' }},
                                                    {{ $claim->insurance->user->claimAddress->city ?? '-' }},
                                                    {{ $claim->insurance->user->claimAddress->address ?? '-' }}
                                                </td>

                                                {{-- Delivery Status --}}
                                                <td>
                                                    @if ($claim->delivery_status === 'approved')
                                                        <button class="btn btn-sm btn-success" type="button" disabled>
                                                            {{ ucfirst($claim->delivery_status) }}
                                                        </button>
                                                    @else
                                                        <div class="dropdown">
                                                            <button
                                                                class="btn btn-sm dropdown-toggle
                                                                @if ($claim->delivery_status === 'rejected') btn-danger 
                                                                @else btn-warning @endif"
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
                                                    @endif
                                                </td>



                                                {{-- Date --}}
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
                paging: true,
                ordering: true,
                responsive: true
            });
        });
    </script>
@endsection
