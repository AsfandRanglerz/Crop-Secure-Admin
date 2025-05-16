@extends('admin.layout.app')
@section('title', 'Villages')
@section('content')
<style>
    .pac-container {
    z-index: 1051 !important; 
}
</style>

    {{-- Add village Modal --}}
    <div class="modal fade" id="villageModal" tabindex="-1" role="dialog" aria-labelledby="villageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="villageModalLabel">Add Village</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('village.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="uc_id" value="{{ $uc->id }}">
    
                        <!-- Village Name -->
                        <div class="form-group">
                            <label for="name">Village Name</label>
                            <input type="text" id="village_name" name="name" class="form-control">
                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">

                        </div>
    
                        <!-- Crop Select -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="crop_name_0">Select Crop</label>
                                <select name="crops[0][crop_name_id]" id="crop_name_0" class="form-control" required>
                                    <option value="">Select Crop</option>
                                    @foreach($crops as $crop)
                                        <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="avg_temp_0">Average Temperature</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="crops[0][avg_temp]" id="avg_temp_0" class="form-control" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">°C</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="avg_rainfall_0">Average Rainfall</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="crops[0][avg_rainfall]" id="avg_rainfall_0" class="form-control" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">mm</span>
                                    </div>
                                </div>
                            </div>
                        </div>                        

                        <div id="cropFieldsWrapper"></div>
                        <button type="button" id="addCropRow" class="btn btn-sm btn-info">Add More</button>
                    </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    


    {{-- Edit village Modal --}}
    @foreach ($villages as $village)
    <div class="modal fade" id="editvillageModal-{{$village->id}}" tabindex="-1" role="dialog" aria-labelledby="editvillageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editvillageModalLabel">Edit Village</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('village.update', $village->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="uc_id" value="{{ $uc->id }}">

                        <!-- Village Name -->
                        <div class="form-group">
                            <label for="message">Village Name</label>
                            <input type="text" id="village_name_edit_{{ $village->id }}" name="name" class="form-control" value="{{ $village->name }}">
                            <input type="hidden" id="latitude_edit_{{ $village->id }}" name="latitude" value="{{ $village->latitude }}">
                            <input type="hidden" id="longitude_edit_{{ $village->id }}" name="longitude" value="{{ $village->longitude }}">
                        </div>

                        <!-- Existing Crops -->
                        @php $editIndex = 0; @endphp
                        @foreach ($village->crops as $crop)
                            <div class="row mb-4">
                                <!-- Crop Name -->
                                <div class="col-md-4">
                                    <label for="crop_name_{{ $editIndex }}" class="form-label">Select Crop</label>
                                    <select name="crops[{{ $editIndex }}][crop_name_id]" id="crop_name_{{ $editIndex }}" class="form-control" required>
                                        <option value="">Select Crop</option>
                                        @foreach($crops as $c)
                                            <option value="{{ $c->id }}" {{ $c->id == $crop->crop_name_id ? 'selected' : '' }}>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Average Temperature -->
                                <div class="col-md-4">
                                    <label for="avg_temp_{{ $editIndex }}" class="form-label">Average Temperature</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" name="crops[{{ $editIndex }}][avg_temp]" id="avg_temp_{{ $editIndex }}"
                                            class="form-control" value="{{ $crop->avg_temp }}" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text" style="border: 1px solid #cbd2d8;">°C</span>
                                            </div>
                                    </div>
                                </div>

                                <!-- Average Rainfall -->
                                <div class="col-md-4">
                                    <label for="avg_rainfall_{{ $editIndex }}" class="form-label">Average Rainfall</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" name="crops[{{ $editIndex }}][avg_rainfall]" id="avg_rainfall_{{ $editIndex }}"
                                            class="form-control" value="{{ $crop->avg_rainfall }}" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text" style="border: 1px solid #cbd2d8;">mm</span>
                                            </div>
                                    </div>
                                </div>
                            </div>
                            @php $editIndex++; @endphp
                        @endforeach


                        <!-- Optional: Button to add more crops in edit modal -->
                        <div id="editCropFieldsWrapper-{{$village->id}}"></div>
                        <button type="button" class="btn btn-sm btn-info addMoreCropsEdit" data-village-id="{{ $village->id }}" data-index="{{ $editIndex }}">Add More</button>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach



    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>{{$uc->name}} - Villages</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                <a class="btn btn-primary mb-3" href="#" data-toggle="modal"
                                                    data-target="#villageModal">Add Village</a>
                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>village</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($villages as $village)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $village->name }}</td>
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        <a href="#"
                                                        data-toggle="modal"
                                                        data-target="#editvillageModal-{{$village->id}}"
                                                            class="btn btn-primary" style="margin-left: 10px">Edit</a>
                                                        <form
                                                            action="
                                                    {{ route('village.destroy', $village->id) }}
                                                     "
                                                            method="POST"
                                                            style="display:inline-block; margin-left: 10px">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="uc_id" value="{{ $uc->id }}">
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

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAeS_6C9oYYmoRemrYTCgureWbaJ3IN-7c&libraries=places"></script>

<script>
    let placeSelected = false;
    const placeSelectedEdit = {}; // Store flags for each edit modal

    function initAutocomplete() {
        const input = document.getElementById('village_name');
        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['(regions)'],
            componentRestrictions: { country: 'pk' }
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (place && place.geometry && place.place_id) {
                placeSelected = true;
                document.getElementById('latitude').value = place.geometry.location.lat();
                document.getElementById('longitude').value = place.geometry.location.lng();
            } else {
                placeSelected = false;
            }
        });

        input.addEventListener('keydown', function () {
            placeSelected = false;
        });
    }

    function initEditAutocomplete(villageId) {
        const input = document.getElementById(`village_name_edit_${villageId}`);
        const latInput = document.getElementById(`latitude_edit_${villageId}`);
        const lngInput = document.getElementById(`longitude_edit_${villageId}`);

        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['(regions)'],
            componentRestrictions: { country: 'pk' }
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (place && place.geometry && place.place_id) {
                placeSelectedEdit[villageId] = true;
                latInput.value = place.geometry.location.lat();
                lngInput.value = place.geometry.location.lng();
            } else {
                placeSelectedEdit[villageId] = false;
            }
        });

        input.addEventListener('keydown', function () {
            placeSelectedEdit[villageId] = false;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initAutocomplete();

        // Validation for Add modal
        document.getElementById('villageModal').addEventListener('submit', function (e) {
            if (!placeSelected) {
                e.preventDefault();
                alert('⚠️ Please select a valid village from Google suggestions.');
                return false;
            }
        });

        // Initialize edit autocomplete and validation
        @foreach($villages as $village)
            initEditAutocomplete({{ $village->id }});

            document.getElementById('editvillageModal-{{ $village->id }}').addEventListener('submit', function (e) {
                if (typeof placeSelectedEdit[{{ $village->id }}] !== 'undefined' && !placeSelectedEdit[{{ $village->id }}]) {
                    e.preventDefault();
                    alert('⚠️ Please select a valid village from Google suggestions.');
                    return false;
                }
            });
        @endforeach
    });
</script>



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

    {{-- for add crops againt village  --}}
    <script>
    let cropIndex = 1;

    $('#addCropRow').click(function () {
        const newFields = `
            <div class="crop-field-group row mb-3">
                <div class="col-md-4">
                    <select name="crops[${cropIndex}][crop_name_id]" class="form-control" required>
                        <option value="">Select Crop</option>
                        @foreach($crops as $crop)
                            <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="number" name="crops[${cropIndex}][avg_temp]" step="0.1" class="form-control" required>
                        <div class="input-group-append">
                            <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">°C</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                   <div class="input-group">
                        <input type="number" name="crops[${cropIndex}][avg_rainfall]" step="0.1" class="form-control" required>
                        <div class="input-group-append">
                            <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">mm</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#cropFieldsWrapper').append(newFields);
        cropIndex++;
    });


    // edit crops against village 
    $('.addMoreCropsEdit').on('click', function () {
        const villageId = $(this).data('village-id');
        let index = $(this).data('index');

        const newFields = `
            <div class="crop-field-group row mb-3">
                <div class="col-md-4">
                    <select name="crops[${index}][crop_name_id]" class="form-control" required>
                        <option value="">Select Crop</option>
                        @foreach($crops as $crop)
                            <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="number" name="crops[${index}][avg_temp]" step="0.1" class="form-control" required>
                        <div class="input-group-append">
                            <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">°C</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="number" name="crops[${index}][avg_rainfall]" step="0.1" class="form-control" required>
                        <div class="input-group-append">
                            <span class="input-group-text font-weight-bold" style="border: 1px solid #cbd2d8;">mm</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $(`#editCropFieldsWrapper-${villageId}`).append(newFields);
        $(this).data('index', index + 1);
    });
</script>


@endsection
