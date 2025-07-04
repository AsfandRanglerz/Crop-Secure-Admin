@extends('admin.layout.app')
@section('title', 'Create Dealer Item')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <a class="btn btn-primary mb-3" href="{{ route('dealer.item.index', $dealer_id) }}">Back</a>
                <form id="add_dealer" action="{{ route('dealer.item.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="dealer_id" value="{{ $dealer_id }}">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="card">
                                <h4 class="text-center my-4">Create Dealer Item</h4>
                                <div class="row mx-0 px-4">
                                    <!-- Name Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="name">Item</label>
                                            <select name="item_id" id="" class="form-control">
                                                <option value="" selected disabled>Select Item</option>
                                                @foreach ($items as $item)
                                                    <option value="{{ $item->id }}">{{ ucfirst($item->name) }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback"></div>
                                            @error('item_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>


                                    <!-- Quantity Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="quantity">Quantity</label>
                                            <input type="text" class="form-control" id="quantity" name="quantity"
                                                value="{{ old('quantity') }}">
                                            <div class="invalid-feedback"></div>
                                            @error('quantity')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- price Field -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group">
                                            <label for="price">Price</label>
                                            <input type="number" class="form-control" id="price" name="price"
                                                value="{{ old('price') }}">
                                            <div class="invalid-feedback"></div>
                                            @error('price')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>


                                    <!-- Status Dropdown -->
                                    <div class="col-sm-6 pl-sm-0 pr-sm-3">
                                        <div class="form-group mb-2">
                                            <label for="status">Status (Optional)</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="" {{ old('status') === null ? 'selected' : '' }}
                                                    disabled>Select an Option</option>
                                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active
                                                </option>
                                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>
                                                    Deactive
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                </div>

                                <!-- Submit Button -->
                                <div class="card-footer text-center row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary mr-1 btn-bg"
                                            id="submit">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@section('js')
    @if (\Illuminate\Support\Facades\Session::has('message'))
        <script>
            toastr.success('{{ \Illuminate\Support\Facades\Session::get('message') }}');
        </script>
    @endif
@endsection
