<div class="main-sidebar sidebar-style-2">
    <aside" id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ url('/admin/dashboard') }}">
                <img alt="image" src="{{ asset('public/admin/assets/img/logo.png') }}" class="header-logo" />
                {{-- <span class="logo-name">Crop Secure</span> --}}
            </a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Main</li>
            <li class="dropdown {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <a href="{{ url('/admin/dashboard') }}" class="nav-link"><i
                        data-feather="home"></i><span>Dashboard</span></a>
            </li>

            @if (Auth::guard('admin')->check())
                {{-- SubAdmin --}}
                <li class="dropdown {{ request()->is('admin/subadmin*') ? 'active' : '' }}">
                    <a href="{{ route('subadmin.index') }}" class="nav-link"><i data-feather="user-check"></i><span>Sub
                            Admins</span></a>
                </li>
            @endif

            @php
                $newDealer = \App\Models\AuthorizedDealer::where('is_seen', false)->count();
            @endphp

            @if (Auth::guard('admin')->check() || $sideMenuName->contains('Authorized Dealers'))
                <li class="dropdown {{ request()->is('admin/dealer*') ? 'active' : '' }}">
                    <a href="{{ route('dealer.index') }}"
                        class="nav-link px-2 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i data-feather="shopping-bag" class="me-2"></i>
                            <span>Authorized Dealers</span>
                        </div>
                        @if ($newDealer > 0)
                            <span
                                class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 22px; height: 22px; font-size: 12px;" title="{{ $newDealer }} pending"
                                aria-label="{{ $newDealer }} new dealers">
                                {{ $newDealer > 99 ? '99+' : $newDealer }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif



            @if (Auth::guard('admin')->check() || $sideMenuName->contains('Dealer Items'))
                {{-- Authorized Dealers --}}
                <li class="dropdown {{ request()->is('admin/items*') ? 'active' : '' }}">
                    <a href="
                        {{ route('items.index') }}
                        "
                        class="nav-link">
                        <i data-feather="layers"></i><span> Dealer Items</span>
                    </a>
                </li>
            @endif

            @if (Auth::guard('admin')->check() || $sideMenuName->contains('Farmers'))
                {{-- Human Resource --}}
                <li class="dropdown {{ request()->is('admin/farmer*') ? 'active' : '' }}">
                    <a href="
                {{ route('farmers.index') }}
                " class="nav-link">
                        <i data-feather="user"></i><span>Farmers</span>
                    </a>
                </li>
            @endif

            @if (Auth::guard('admin')->check() || $sideMenuName->contains('Insured Crops'))
                <li
                    class="dropdown {{ request()->is('admin/ensured-crop-name*') || request()->is('admin/crop-type*') ? 'active' : '' }}">
                    <a href="
                {{ route('ensured.crop.name.index') }}
                "
                        class="nav-link  px-2">
                        <i class="fas fa-leaf"></i><span> Insured Crops</span>
                    </a>
                </li>
            @endif

            @if (Auth::guard('admin')->check() || $sideMenuName->contains('Land Data Management'))
                {{-- Company --}}
                <li
                    class="dropdown {{ request()->is('admin/land-data-management*') || request()->is('admin/village*') || request()->is('admin/union*') || request()->is('admin/tehsil*') || request()->is('admin/unit*') ? 'active' : '' }}">
                    <a href="
                {{ route('land.index') }}
                " class="nav-link">
                        <i data-feather="map"></i><span>Land Data Management</span>
                    </a>
                </li>
            @endif

            @if (Auth::guard('admin')->check() || $sideMenuName->contains('Insurance Companies'))
                {{-- Demands --}}
                <li
                    class="dropdown {{ request()->is('admin/insurance-company*') || request()->is('admin/company-insurance*') ? 'active' : '' }}">
                    <a href="
                {{ route('insurance.company.index') }}
                "
                        class="nav-link px-2">
                        <i class="fas fa-shield-alt"></i> <span>Insurance Companies</span>
                    </a>
                </li>
            @endif

            {{-- Insurance Types & Sub-types --}}
            @if (Auth::guard('admin')->check() || $sideMenuName->contains('Insurance Types'))
                <li
                    class="dropdown {{ request()->is('admin/insurance-type*') || request()->is('admin/insurance-sub-type*') ? 'active' : '' }}">
                    <a href="
                {{ route('insurance.type.index') }}
                "
                        class="nav-link px-2">
                        <i class="fas fa-cogs"></i> <span>Insurance Types</span>
                    </a>
                </li>
            @endif

            @php
                $newProductClaimCount = \App\Models\InsuranceProductClaim::where('delivery_status', 'pending')->count();
            @endphp

            @if (Auth::guard('admin')->check() || $sideMenuName->contains('Claim Product Purchase'))
                <li class="dropdown {{ request()->is('admin/insurance-product-claims*') ? 'active' : '' }}">
                    <a href="{{ route('insurance.product.claims.index') }}"
                        class="nav-link px-2 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-box-open me-2"></i>
                            <span>Claim Product Purchases</span>
                        </div>
                        @if ($newProductClaimCount > 0)
                            <span
                                class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 22px; height: 22px; font-size: 12px;"
                                title="{{ $newProductClaimCount }} pending">
                                {{ $newProductClaimCount > 99 ? '99+' : $newProductClaimCount }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif

            @php
                $showInsuranceClaimRequests =
                    Auth::guard('admin')->check() || $sideMenuName->contains('Insurance Claim Requests');
                $onClaimPage = request()->is('admin/insurance-claim*');
                $newClaimCount = 0;

                if ($showInsuranceClaimRequests) {
                    $newClaimCount = \App\Models\InsuranceHistory::whereNotNull('claimed_at')
                        // ->where(function ($q) {
                        //     $q->where('is_claim_seen', 0)->orWhereNull('is_claim_seen');
                        // })
                        ->where('status', 'pending')
                        ->count();
                }
            @endphp
            @if ($showInsuranceClaimRequests)
                <li class="dropdown {{ $onClaimPage ? 'active' : '' }}">
                    <a href="{{ route('insurance.claim.index') }}"
                        class="nav-link px-2 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-alt me-2"></i>
                            <span>Insurance Claim Requests</span>
                        </div>
                        @if ($newClaimCount > 0)
                            <span
                                class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 22px; height: 22px; font-size: 12px;" title="{{ $newClaimCount }} new">
                                {{ $newClaimCount > 99 ? '99+' : $newClaimCount }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif


            {{-- Insurance History --}}
            @php
                $showInsuranceHistory = Auth::guard('admin')->check() || $sideMenuName->contains('Insurance History');
                $onInsurancePage = request()->is('admin/insurance-history*');
                $newInsuranceCount = 0;

                if ($showInsuranceHistory && !$onInsurancePage) {
                    $newInsuranceCount = \App\Models\InsuranceHistory::where(function ($query) {
                        $query->where('is_seen', 0)->orWhereNull('is_seen');
                    })->count();
                }
            @endphp

            @if ($showInsuranceHistory)
                <li class="dropdown {{ $onInsurancePage ? 'active' : '' }}">
                    <a href="{{ route('insurance.history.index') }}"
                        class="nav-link px-2 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-shield me-2"></i>
                            <span>Purchased Insurances</span>
                        </div>
                        @if ($newInsuranceCount > 0)
                            <span
                                class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 22px; height: 22px; font-size: 12px;"
                                title="{{ $newInsuranceCount }} new">
                                {{ $newInsuranceCount > 99 ? '99+' : $newInsuranceCount }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif


            @if (Auth::guard('admin')->check() || $sideMenuName->contains('Notifications'))
                {{-- Notifications --}}
                <li class="dropdown {{ request()->is('admin/notification*') ? 'active' : '' }}">
                    <a href="
                {{ route('notification.index') }}
                " class="nav-link">
                        <i data-feather="bell"></i><span>Notifications</span>
                    </a>
                </li>
            @endif

            @if (Auth::guard('admin')->check() || $sideMenuName->contains('faqs'))
                <!-- Terms & Conditions Section -->
                <li class="dropdown {{ request()->is('admin/faqs*') ? 'active' : '' }}">
                    <a href="{{ url('admin/faqs') }}" class="nav-link">
                        <i data-feather="help-circle"></i><span>FAQ's</span>
                    </a>
                </li>
            @endif

            @if (Auth::guard('admin')->check() || $sideMenuName->contains('ContactUs'))
                <!-- Terms & Conditions Section -->
                <li class="dropdown {{ request()->is('admin/contact-us*') ? 'active' : '' }}">
                    <a href="{{ url('admin/contact-us') }}" class="nav-link">
                        <i data-feather="mail"></i><span>Contact Us</span>
                    </a>
                </li>
            @endif


            <li class="dropdown {{ request()->is('admin/about-us*') ? 'active' : '' }}">
                <a href="{{ url('/admin/about-us') }}" class="nav-link"><i data-feather="info"></i><span>About
                        Us</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/privacy-policy*') ? 'active' : '' }}">
                <a href="{{ url('/admin/privacy-policy') }}" class="nav-link"><i
                        data-feather="file-text"></i><span>Privacy Policy</span></a>
            </li>
            <li class="dropdown {{ request()->is('admin/term-condition*') ? 'active' : '' }}">
                <a href="{{ url('/admin/term-condition') }}" class="nav-link"><i
                        data-feather="clipboard"></i><span>Terms & Conditions</span></a>
            </li>
        </ul>
        </aside>
</div>
