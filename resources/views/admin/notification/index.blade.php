@extends('admin.layout.app')
@section('title', 'Notifications')

@section('content')
    <style>
        .select2-container {
            display: block;
        }

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


    <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('notification.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Notification</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        {{-- <div class="form-group">
                        <label for="userType">Select User Type</label>
                        <select class="form-control" name="user_type[]" id="userType" multiple required>
                            <option value="farmers">Farmers</option>
                          <!--  <option value="authorized_dealers">Authorized Dealers</option>-->
                        </select>
                    </div> --}}

                        <div class="form-group" id="farmers-group">
                            <label for="farmers">Select Farmers</label>
                            <div>
                                <input type="checkbox" id="selectAllFarmers">
                                <label for="selectAllFarmers">Select All</label>
                            </div>
                            <select class="form-control" id="farmers" name="farmers[]" multiple>
                                @foreach ($farmers as $farmer)
                                    <option value="{{ $farmer->id }}">{{ $farmer->name }}</option>
                                @endforeach
                            </select>
                            @error('farmers')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="edit_title">Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title">
                            <div class="invalid-feedback"></div>
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="form-group">
                            <label for="message">Message (Optional)</label>
                            <textarea name="message" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Send</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4>Notifications</h4>
                </div>
                <div class="card-body table-striped table-bordered table-responsive">
                    @if (Auth::guard('admin')->check() ||
                            $sideMenuPermissions->contains(fn($p) => $p['side_menu_name'] === 'Notifications' && $p['permissions']->contains('create')))
                        <a class="btn btn-primary mb-3 text-white" data-toggle="modal"
                            data-target="#notificationModal">Create</a>

                        <form id="deleteAllForm" action="{{ route('notification.deleteAll') }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger mb-3 ml-2" id="deleteAllBtn">Delete All</button>
                        </form>
                    @endif

                    <table class="table responsive" id="table_id_events">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Name</th>
                                <th>Title</th>
                                <th>Message</th>
                                {{-- <th>User Type</th> --}}
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notifications as $index => $notification)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @php
                                            $names = [];
                                            foreach ($notification->targets as $target) {
                                                if (
                                                    $target->targetable_type === \App\Models\Farmer::class &&
                                                    isset($farmers[$target->targetable_id])
                                                ) {
                                                    $names[] = $farmers[$target->targetable_id]->name;
                                                }
                                            }

                                            $totalNames = count($names);
                                            $displayNames = array_slice($names, 0, 2); // Show first 2 names by default
                                        @endphp

                                        @foreach ($displayNames as $name)
                                            <span class="name-preview">{{ $name }}</span><br>
                                        @endforeach

                                        @if ($totalNames > 2)
                                            <span class="additional-names d-none">
                                                @foreach (array_slice($names, 2) as $name)
                                                    {{ $name }}<br>
                                                @endforeach
                                            </span>
                                            <a href="#" class="toggle-names" data-count="{{ $totalNames - 2 }}">
                                                {{ $totalNames - 2 }}+
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $notification->title }}</td>
                                    <td>
                                        @php
                                            $words = explode(' ', $notification->message);
                                            $shortMessage = implode(' ', array_slice($words, 0, 3));
                                            $showReadMore = count($words) > 3;
                                        @endphp

                                        <span class="message-preview">{{ $shortMessage }}</span>
                                        @if ($showReadMore)
                                            <span class="full-message d-none">{{ $notification->message }}</span>
                                            <a href="#" class="read-more-link">... Read More</a>
                                        @endif
                                    </td>
                                    {{-- <td>
                                        @if (is_array($notification->user_type))
                                            {{ implode(', ', $notification->user_type) }}
                                        @else
                                            {{ $notification->user_type ?? 'N/A' }}
                                        @endif
                                    </td> --}}

                                    <td>
                                        {{-- <button class="btn btn-sm btn-primary editBtn" data-id="{{ $notification->id }}"
                                            data-message="{{ $notification->message }}"
                                            data-user-type="{{ json_encode($notification->user_type) }}"
                                            data-farmers="{{ json_encode($notification->targets->where('targetable_type', \App\Models\Farmer::class)->pluck('targetable_id')->toArray()) }}">
                                            Edit
                                        </button> --}}

                                        <form action="{{ route('notification.destroy', $notification->id) }}"
                                            method="POST" class="deleteForm d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="btn btn-sm btn-danger show_confirm">Delete</button>
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
        $(document).ready(function() {
            // Initialize select2 dropdown
            $('#farmers, #edit_farmers').select2({
                placeholder: 'Select Farmers',
                allowClear: true
            });

            $('#table_id_events').DataTable();

            // Select all farmers
            $('#selectAllFarmers').change(function() {
                $('#farmers option').prop('selected', this.checked).trigger('change');
            });

            $('#edit_selectAllFarmers').change(function() {
                $('#edit_farmers option').prop('selected', this.checked).trigger('change');
            });

            // Edit button logic (updated only for farmers)
            $('.editBtn').on('click', function() {
                const id = $(this).data('id');
                const message = $(this).data('message');
                const farmers = $(this).data('farmers');
                const title = $(this).data('title');

                $('#edit_notification_id').val(id);
                $('#edit_message').val(message);
                $('#edit_title').val(title);

                if (farmers && farmers.length > 0) {
                    $('#edit_farmers').val(farmers).trigger('change');
                } else {
                    $('#edit_farmers').val(null).trigger('change');
                }

                $('#edit_selectAllFarmers').prop('checked', $('#edit_farmers option').length === $(
                    '#edit_farmers option:selected').length);

                $('#editNotificationForm').attr('action', '{{ route('notification.update', '') }}/' + id);
                $('#editNotificationModal').modal('show');
            });

            // Read more toggle
            $(document).on('click', '.read-more-link', function(e) {
                e.preventDefault();
                const $parent = $(this).parent();
                $parent.find('.message-preview').addClass('d-none');
                $parent.find('.full-message').removeClass('d-none');
                $(this).remove(); // Remove "Read More" link
            });

            // Show more/less names
            $(document).on('click', '.toggle-names', function(e) {
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

            // Toastr notifications
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

    <script>
        // ✅ Create Form Validation
        $('form[action="{{ route('notification.store') }}"]').on('submit', function(e) {
            let isValid = true;

            const farmerSelect = $('#farmers');
            const titleInput = $('#edit_title');

            // Remove previous errors
            farmerSelect.next('.text-danger').remove();
            titleInput.next('.text-danger').remove();

            // Validate Farmers
            if (farmerSelect.val() === null || farmerSelect.val().length === 0) {
                farmerSelect.after('<span class="text-danger">Please select at least one farmer.</span>');
                isValid = false;
            }

            // Validate Title
            if ($.trim(titleInput.val()) === '') {
                titleInput.after('<span class="text-danger">Title field is required.</span>');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // ✅ Edit Form Validation
        $('#editNotificationForm').on('submit', function(e) {
            let isValid = true;

            const farmerSelect = $('#edit_farmers');
            const titleInput = $('#edit_title');

            // Remove previous errors
            farmerSelect.next('.text-danger').remove();
            titleInput.next('.text-danger').remove();

            // Validate Farmers
            if (farmerSelect.val() === null || farmerSelect.val().length === 0) {
                farmerSelect.after('<span class="text-danger">Please select at least one farmer.</span>');
                isValid = false;
            }

            // Validate Title
            if ($.trim(titleInput.val()) === '') {
                titleInput.after('<span class="text-danger">Title field is required.</span>');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>

    <script>
        $('#deleteAllBtn').click(function(e) {
            e.preventDefault();

            swal({
                title: "Are you sure?",
                text: "This will delete all notifications permanently!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $('#deleteAllForm').submit();
                }
            });
        });
    </script>
@endsection
