@extends('admin.layout.app')
@section('title', 'Dashboard')
@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
            <div class="row mb-3">
                @if (Auth::guard('admin')->check() ||
                        (isset($sideMenuPermissions['Farmers']) && $sideMenuPermissions['Farmers']['permissions']->contains('view')))
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <a href="{{ route('farmers.index') }}" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row">
                                            <div class="col-lg-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Total Farmers</h5>
                                                    <h2 class="mb-3 font-18">{{ $totalFarmers }}</h2>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pl-0">
                                                <div class="banner-img">
                                                    <img src="{{ asset('public/admin/assets/img/banner/1.png') }}"
                                                        alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif

                @if (Auth::guard('admin')->check() ||
                        (isset($sideMenuPermissions['Authorized Dealers']) &&
                            $sideMenuPermissions['Authorized Dealers']['permissions']->contains('view')))
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <a href="{{ route('dealer.index') }}" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row">
                                            <div class="col-lg-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Total Authorized Dealers</h5>
                                                    <h2 class="mb-3 font-18">{{ $totalDealers }}</h2>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pl-0">
                                                <div class="banner-img">
                                                    <img src="{{ asset('public/admin/assets/img/banner/2.png') }}"
                                                        alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif

                @if (Auth::guard('admin')->check() ||
                        (isset($sideMenuPermissions['Insurance History']) &&
                            $sideMenuPermissions['Insurance History']['permissions']->contains('view')))
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <a href="{{ route('ensured.crops.index') }}" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row">
                                            <div class="col-lg-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Total Crops Insurance</h5>
                                                    <h2 class="mb-3 font-18">{{ $totalInsuranceCrops }}</h2>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pl-0">
                                                <div class="banner-img">
                                                    <img src="{{ asset('public/admin/assets/img/banner/3.png') }}"
                                                        alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
