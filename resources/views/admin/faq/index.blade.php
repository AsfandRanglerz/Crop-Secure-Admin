@extends('admin.layout.app')
@section('title', "FAQ's")
@section('content')
    <style>
        .toggle-faq {
            color: #007bff;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
        }

        .toggle-faq:hover {
            text-decoration: underline;
        }

        .d-none {
            display: none;
        }
    </style>
    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>FAQ's</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                <div class="clearfix">
                                    <div class="create-btn">
                                        @if (Auth::guard('admin')->check() ||
                                                ($sideMenuPermissions->has('faq') && $sideMenuPermissions['faq']->contains('create')))
                                            <a class="btn btn-primary mb-3 text-white"
                                                href="{{ url('admin/faq-create') }}">Create</a>
                                        @endif
                                    </div>
                                </div>

                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th></th> <!-- Sort handle column -->
                                            <th>Sr.</th>
                                            <th>Question</th>
                                            <th>Answer</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-faqs">
                                        @foreach ($faqs as $faq)
                                            <tr data-id="{{ $faq->id }}">
                                                <td class="sort-handler" style="cursor: move; text-align: center;">
                                                    <i class="fas fa-th"></i>
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit(strip_tags($faq->question), 50, '...') }}
                                                <td>
                                                    @php
                                                        $fullAnswer = strip_tags($faq->answer);
                                                        $words = explode(' ', $fullAnswer);
                                                        $shortAnswer = implode(' ', array_slice($words, 0, 5));
                                                        $hasMore = count($words) > 5;
                                                    @endphp

                                                    <span class="faq-preview">{{ $shortAnswer }}</span>

                                                    @if ($hasMore)
                                                        <span class="faq-full d-none">{{ $fullAnswer }}</span>
                                                        <a href="#" class="faq-toggle toggle-faq">... Read More</a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-4">
                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'faq' && $permission['permissions']->contains('edit')))
                                                            <a href="{{ route('faq.edit', $faq->id) }}"
                                                                class="btn btn-primary" style="margin-left: 10px">Edit</a>
                                                        @endif

                                                        @if (Auth::guard('admin')->check() ||
                                                                $sideMenuPermissions->contains(fn($permission) => $permission['side_menu_name'] === 'faq' &&
                                                                        $permission['permissions']->contains('delete')))
                                                            <form action="{{ route('faq.destroy', $faq->id) }}"
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

@endsection

@section('js')

    <!-- DataTables -->
    <script>
        $(document).ready(function() {
            $('#table_id_events').DataTable({
                paging: true,
                info: false
            });
        });
    </script>

    <!-- SweetAlert -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script type="text/javascript">
        $('.show_confirm').click(function(event) {
            var form = $(this).closest("form");
            event.preventDefault();
            swal({
                    title: "Are you sure you want to delete this record?",
                    text: "If you delete this FAQ, it will be gone forever.",
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

    <!-- SortableJS for Drag-and-Drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        window.addEventListener('load', () => {
            const message = localStorage.getItem('toastMessage');
            if (message) {
                toastr.success(message);
                localStorage.removeItem('toastMessage');
            }
        });

        new Sortable(document.getElementById('sortable-faqs'), {
            animation: 150,
            handle: '.sort-handler',
            onEnd: function() {
                let order = [];
                document.querySelectorAll('#sortable-faqs tr').forEach((row, index) => {
                    order.push({
                        id: row.getAttribute('data-id'),
                        position: index + 1
                    });
                });

                fetch("{{ route('faq.reorder') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order: order
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        localStorage.setItem('toastMessage', 'Order Updated Successfully');
                        window.location.reload();
                    });
            }
        });
    </script>
    @if (session('success'))
        <script>
            toastr.success("{{ session('success') }}");
        </script>
    @endif

    @if (session('error'))
        <script>
            toastr.error("{{ session('error') }}");
        </script>
    @endif
    <script>
        $(document).on('click', '.toggle-faq', function(e) {
            e.preventDefault();

            const $row = $(this).closest('td');
            const $preview = $row.find('.faq-preview');
            const $full = $row.find('.faq-full');

            if ($preview.hasClass('d-none')) {
                // Show preview again
                $preview.removeClass('d-none');
                $full.addClass('d-none');
                $(this).text('... Read More');
            } else {
                // Show full
                $preview.addClass('d-none');
                $full.removeClass('d-none');
                $(this).text('Read Less');
            }
        });
    </script>

@endsection
