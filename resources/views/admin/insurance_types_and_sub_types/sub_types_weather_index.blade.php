@extends('admin.layout.app')
@section('title', 'Weather Index')

@section('content')

    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <a class="btn btn-primary mb-3" href="{{ route('insurance.type.index') }}">Back</a>
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4 class="mb-0">{{ $InsuranceType->name }}</h4>
                                    <p class="mt-2 mb-0 text-danger" style="font-style: italic; font-size: 14px;">
                                        <strong>Loss Trigger Conditions:</strong> If the temperature in any village consistently exceeds its average temperature by 20% or more for 14 consecutive days, an alert will be triggered.</br>
                                        If the total rainfall in a village during the season is 50% more or 50% less than the village's average seasonal rainfall, the system will trigger a rainfall alert.</br>
                                    </br><strong>Note:</strong> Rainfall is calculated as the <u>total sum for each day</u>,
                                        and then the
                                        <u>14-day total</u> is compared against the village’s average rainfall.<br>
                                        Temperature is averaged <u>daily</u>, and then a <u>14-day average</u> is compared
                                        against the village’s
                                        average temperature.
                                    </p>
                                </div>
                            </div>

                            <div class="card-body table-striped table-bordered table-responsive">
                                <table class="table responsive" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Villages</th>
                                            <th>Results</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($InsuranceSubTypes as $InsuranceSubType)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $InsuranceSubType->name ?? 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.insurance.result', $InsuranceSubType->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> {{-- card-body --}}
                        </div> {{-- card --}}
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#table_id_events').DataTable();
        });
    </script>
@endsection
