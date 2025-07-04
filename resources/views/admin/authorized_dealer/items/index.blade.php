@extends('admin.layout.app')
@section('title', 'Authorized Dealer Items')
@section('content')


    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <a class="btn btn-primary mb-3" href="{{ route('dealer.index') }}">Back</a>
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>{{ $dealer->name }} - Items</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                <a class="btn btn-primary mb-3 text-white" href="{{ route('dealer.item.create', $dealer_id) }}">Create</a>
                                <table class="table text-center" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dealerItems as $items)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ ucfirst($items->item->name) }}</td>
                                                <td>{{ number_format($items->quantity) }}</td>
                                                <td>{{ number_format($items->price) }} PKR</td>
                                                <td>
                                                    @if ($items->status == 1)
                                                    <div class="badge badge-success badge-shadow">Activated</div>
                                                    @else
                                                    <div class="badge badge-danger badge-shadow">Deactivated</div>
                                                @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        <a href="
                                                    {{ route('dealer.item.edit', ['dealer_id' => $dealer_id, 'item_id' => $items->id]) }}
                                                    "
                                                            class="btn btn-primary" style="margin-left: 10px">Edit</a>
                                                        <form action="{{ route('dealer.item.destroy', [$items->id]) }}"
                                                        method="POST"
                                                            style="display:inline-block; margin-left: 10px">
                                                            @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="dealer_id" value="{{ $dealer_id }}">
                                                            <button type="submit"
                                                                class="btn btn-danger btn-flat show_confirm"
                                                                data-toggle="tooltip">Delete</button>
                                                        </form>
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
