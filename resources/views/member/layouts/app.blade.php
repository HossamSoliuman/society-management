<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Resident Portal') - Society Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @stack('styles')
</head>
<body>
    @php
        $portalUser = auth()->user();
        $portalMember = $portalUser?->relationLoaded('member') ? $portalUser->member : $portalUser?->member;
        $portalSociety = $portalMember?->society;
    @endphp
    <div class="app-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-6h6v6"/>
                        <path d="M9 9h1v1H9zM14 9h1v1h-1z"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-title">{{ Str::limit($portalSociety?->name ?? 'Society', 22) }}</span>
                    <span class="brand-subtitle">Resident Portal</span>
                </div>
            </div>

            <div class="sidebar-label">MY HOME</div>

            <nav class="sidebar-nav">
                <a href="{{ route('member.dashboard') }}" class="nav-item {{ request()->routeIs('member.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('member.bills.index') }}" class="nav-item {{ request()->routeIs('member.bills.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i>
                    <span>My Bills</span>
                </a>
                <a href="{{ route('member.payments.index') }}" class="nav-item {{ request()->routeIs('member.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-receipt"></i>
                    <span>My Payments</span>
                </a>
                <a href="{{ route('member.support.index') }}" class="nav-item {{ request()->routeIs('member.support.*') ? 'active' : '' }}">
                    <i class="fas fa-headset"></i>
                    <span>Complaints & Requests</span>
                </a>
                <a href="{{ route('member.notices.index') }}" class="nav-item {{ request()->routeIs('member.notices.*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn"></i>
                    <span>Notices</span>
                </a>

                <div class="sidebar-label" style="margin-top: 12px;">MY HOUSEHOLD</div>
                <a href="{{ route('member.family.index') }}" class="nav-item {{ request()->routeIs('member.family.*') ? 'active' : '' }}">
                    <i class="fas fa-people-roof"></i>
                    <span>Family Members</span>
                </a>
                <a href="{{ route('member.vehicles.index') }}" class="nav-item {{ request()->routeIs('member.vehicles.*') ? 'active' : '' }}">
                    <i class="fas fa-car"></i>
                    <span>Vehicles</span>
                </a>
                <a href="{{ route('member.profile') }}" class="nav-item {{ request()->routeIs('member.profile*') ? 'active' : '' }}">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="main-content">
            <header class="top-header">
                <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="header-search" style="visibility: hidden;"></div>

                <div class="header-actions">
                    <div class="profile-dropdown">
                        <button class="profile-btn">
                            <div class="avatar">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($portalUser->name ?? 'Resident') }}&background=E84B1E&color=fff" alt="">
                            </div>
                            <div class="profile-info">
                                <span class="profile-name">{{ $portalUser->name ?? 'Resident' }}</span>
                                <span class="profile-role">{{ $portalMember?->flat_unit ? 'Flat '.$portalMember->flat_unit : 'Resident' }}{{ $portalMember?->tower_wing ? ' · '.$portalMember->tower_wing : '' }}</span>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="{{ route('member.profile') }}" class="dropdown-menu-item">
                                <i class="fas fa-user"></i>
                                <span>My Profile</span>
                            </a>
                            <hr class="dropdown-menu-divider">
                            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="dropdown-menu-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="page-content">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
