@extends('admin.layout.app')
@section('title', 'Notifications')

@section('content')
<style>
    .select2-container { display: block; }
    .read-more-link {
    color: #007bff;
    cursor: pointer;
    text-decoration: none;
}
.read-more-link:hover {
    text-decoration: underline;
}
.d-none {
    display: none;
}
.toggle-names {
    color: #007bff;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.9em;
    margin-left: 4px;
}
.toggle-names:hover {
    text-decoration: underline;
}
.d-none {
    display: none;
}
.name-preview {
    display: inline-block;
    margin-right: 5px;
}



</style>


<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('notification.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Notification</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="userType">Select User Type</label>
                        <select class="form-control" name="user_type[]" id="userType" multiple required>
                            <option value="farmers">Farmers</option>
                          <!--  <option value="authorized_dealers">Authorized Dealers</option>-->
                        </select>
                    </div>

                    <div class="form-group d-none" id="farmers-group">
                        <label for="farmers">Select Farmers</label>
                        <div>
                            <input type="checkbox" id="selectAllFarmers"> <label for="selectAllFarmers">Select All</label>
                        </div>
                        <select class="form-control" id="farmers" name="farmers[]" multiple>
                            @foreach ($farmers as $farmer)
                                <option value="{{ $farmer->id }}">{{ $farmer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                   

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Send Notification</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="main-content">
    <section class="section">
        <div class="card">
            <div class="card-header"><h4>Notifications</h4></div>
            <div class="card-body table-responsive">
                @if (Auth::guard('admin')->check() || $sideMenuPermissions->contains(fn($p) => $p['side_menu_name'] === 'Notifications' && $p['permissions']->contains('create')))
                    <a class="btn btn-primary mb-3 text-white" data-toggle="modal" data-target="#notificationModal">Create</a>
                @endif

                <table class="table table-bordered text-center" id="table_id_events">
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Message</th>
                            <th>User Type</th>
                            <th>Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notifications as $index => $notification)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
    @php
        $words = explode(' ', $notification->message);
        $shortMessage = implode(' ', array_slice($words, 0, 3));
        $showReadMore = count($words) > 3;
    @endphp
    
    <span class="message-preview">{{ $shortMessage }}</span>
    @if($showReadMore)
        <span class="full-message d-none">{{ $notification->message }}</span>
        <a href="#" class="read-more-link">... Read More</a>
    @endif
</td>
                                <td>
                                    @if(is_array($notification->user_type))
                                        {{ implode(', ', $notification->user_type) }}
                                    @else
                                        {{ $notification->user_type ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>
    @php
        $names = [];
        foreach ($notification->targets as $target) {
            if ($target->targetable_type === \App\Models\Farmer::class && isset($farmers[$target->targetable_id])) {
                $names[] = $farmers[$target->targetable_id]->name;
            }
        }

        $totalNames = count($names);
        $displayNames = array_slice($names, 0, 2); // Show first 2 names by default
    @endphp

    @foreach($displayNames as $name)
        <span class="name-preview">{{ $name }}</span><br>
    @endforeach

    @if($totalNames > 2)
        <span class="additional-names d-none">
            @foreach(array_slice($names, 2) as $name)
                {{ $name }}<br>
            @endforeach
        </span>
        <a href="#" class="toggle-names" data-count="{{ $totalNames - 2 }}">
            {{ $totalNames - 2 }}+
        </a>
    @endif
</td>

                                
     
                                <td>
                                    <button 
                                        class="btn btn-sm btn-primary editBtn"
                                        data-id="{{ $notification->id }}"
                                        data-message="{{ $notification->message }}"
                                        data-user-type="{{ json_encode($notification->user_type) }}"
                                        data-farmers="{{ json_encode($notification->targets->where('targetable_type', \App\Models\Farmer::class)->pluck('targetable_id')->toArray()) }}"
                                        
                                    >
                                        Edit
                                    </button>

                                    <form action="{{ route('notification.destroy', $notification->id) }}" method="POST" class="deleteForm d-inline">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-sm btn-danger show_confirm">Delete</button>
</form>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editNotificationModal" tabindex="-1" role="dialog" aria-labelledby="editNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="editNotificationForm" method="POST">
            @csrf
            @method('POST')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Notification</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="notification_id" id="edit_notification_id">

                    <div class="form-group">
    <label>User Type</label>
    <p class="form-control-plaintext">Farmers</p>
    <input type="hidden" name="user_type[]" value="farmers">
</div>


                    <div class="form-group">
                        <label for="edit_farmers">Select Farmers</label>
                        <div>
                            <input type="checkbox" id="edit_selectAllFarmers">
                            <label for="edit_selectAllFarmers">Select All</label>
                        </div>
                        <select class="form-control" id="edit_farmers" name="farmers[]" multiple>
                            @foreach ($farmers as $farmer)
                                <option value="{{ $farmer->id }}"
                                    {{ isset($selectedFarmerIds) && in_array($farmer->id, $selectedFarmerIds) ? 'selected' : '' }}>
                                    {{ $farmer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_message">Message</label>
                        <textarea name="message" class="form-control" id="edit_message" rows="5" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Update Notification</button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

{{-- JavaScript Section --}}
<script>
    $(document).ready(function () {
        // Initialize select2 dropdowns
        $('#userType, #farmers, #dealers, #edit_userType, #edit_farmers, #edit_dealers').select2({
            placeholder: 'Select',
            allowClear: true
        });

        $('#table_id_events').DataTable();

        // Toggle user type fields
        $('#userType').change(function () {
            const selected = $(this).val();
            $('#farmers-group').toggleClass('d-none', !selected.includes('farmers'));
           
        });

        $('#edit_userType').change(function () {
            const selected = $(this).val();
            $('#edit-farmers-group').toggleClass('d-none', !selected.includes('farmers'));
           
        });

        // Select all farmers/dealers
        $('#selectAllFarmers').change(function () {
            $('#farmers option').prop('selected', this.checked).trigger('change');
        });
        $('#selectAllDealers').change(function () {
            $('#dealers option').prop('selected', this.checked).trigger('change');
        });
        $('#edit_selectAllFarmers').change(function () {
            $('#edit_farmers option').prop('selected', this.checked).trigger('change');
        });
        $('#edit_selectAllDealers').change(function () {
            $('#edit_dealers option').prop('selected', this.checked).trigger('change');
        });

        // Edit button logic
        $('.editBtn').on('click', function () {
            const id = $(this).data('id');
            const message = $(this).data('message');
            const userTypes = $(this).data('user-type');
            const farmers = $(this).data('farmers');
            const dealers = $(this).data('dealers');

            $('#edit_notification_id').val(id);
            $('#edit_message').val(message);
            $('#edit_userType').val(userTypes).trigger('change');

            if (farmers && farmers.length > 0) {
                $('#edit_farmers').val(farmers).trigger('change');
                $('#edit-farmers-group').removeClass('d-none');
            } else {
                $('#edit_farmers').val(null).trigger('change');
                $('#edit-farmers-group').addClass('d-none');
            }

            if (dealers && dealers.length > 0) {
                $('#edit_dealers').val(dealers).trigger('change');
                $('#edit-dealers-group').removeClass('d-none');
            } else {
                $('#edit_dealers').val(null).trigger('change');
                $('#edit-dealers-group').addClass('d-none');
            }

            $('#edit_selectAllFarmers').prop('checked', $('#edit_farmers option').length === $('#edit_farmers option:selected').length);
            $('#edit_selectAllDealers').prop('checked', $('#edit_dealers option').length === $('#edit_dealers option:selected').length);

            $('#editNotificationForm').attr('action', '{{ route('notification.update', '') }}/' + id);
            $('#editNotificationModal').modal('show');
        });

        // Read more toggle
        $(document).on('click', '.read-more-link', function (e) {
            e.preventDefault();
            const $parent = $(this).parent();
            $parent.find('.message-preview').addClass('d-none');
            $parent.find('.full-message').removeClass('d-none');
            $(this).remove(); // Remove "Read More" link
        });

        // Show more/less names
        $(document).on('click', '.toggle-names', function (e) {
            e.preventDefault();
            const $this = $(this);
            const $additionalNames = $this.prev('.additional-names');
            const count = $this.data('count');

            if ($additionalNames.hasClass('d-none')) {
                $additionalNames.removeClass('d-none');
                $this.text('Show less');
            } else {
                $additionalNames.addClass('d-none');
                $this.text(count + '+');
            }
        });   


        // Toastr notifications (create/edit/delete)
        @if (session('success'))
            toastr.success(@json(session('success')));
        @endif

        @if (session('error'))
            toastr.error(@json(session('error')));
        @endif

        @if (session('info'))
            toastr.info(@json(session('info')));
        @endif
    });
</script>


@endsection