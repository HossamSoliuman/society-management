<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Society Management SaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
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
                    <span class="brand-title">Society Management</span>
                    <span class="brand-subtitle">SaaS Platform</span>
                </div>
            </div>

            <div class="sidebar-label">SUPER ADMIN</div>

            <nav class="sidebar-nav">
                <a href="{{ route('superadmin.dashboard') }}" class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('superadmin.societies.index') }}" class="nav-item {{ request()->routeIs('superadmin.societies.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span>Society Management</span>
                </a>

                <a href="{{ route('superadmin.subscription.subscriptions') }}" class="nav-item {{ request()->routeIs('superadmin.subscription.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i>
                    <span>Subscription Management</span>
                </a>

                <a href="{{ route('superadmin.billing.overview') }}" class="nav-item {{ request()->routeIs('superadmin.billing.*') ? 'active' : '' }}">
                    <i class="fas fa-indian-rupee-sign"></i>
                    <span>Revenue & Billing</span>
                </a>

                <a href="{{ route('superadmin.users.index') }}" class="nav-item {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>

                <a href="{{ route('superadmin.reports.index') }}" class="nav-item {{ request()->routeIs('superadmin.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>

                <a href="{{ route('superadmin.notification.announcements') }}" class="nav-item {{ request()->routeIs('superadmin.notification.*') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <span class="badge">12</span>
                </a>

                <a href="{{ route('superadmin.tickets.index') }}" class="nav-item {{ request()->routeIs('superadmin.tickets.*') ? 'active' : '' }}">
                    <i class="fas fa-headset"></i>
                    <span>Support Tickets</span>
                </a>

                <a href="{{ route('superadmin.logs.user-activities') }}" class="nav-item {{ request()->routeIs('superadmin.logs.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Activity Logs</span>
                </a>

                <a href="{{ route('superadmin.masters.index') }}" class="nav-item {{ request()->routeIs('superadmin.masters.*') ? 'active' : '' }}">
                    <i class="fas fa-database"></i>
                    <span>Masters</span>
                </a>

                <a href="{{ route('superadmin.terms.index') }}" class="nav-item {{ request()->routeIs('superadmin.terms.*') ? 'active' : '' }}">
                    <i class="fas fa-file-contract"></i>
                    <span>Terms & Conditions</span>
                </a>

                <a href="{{ route('superadmin.settings.company-profile') }}" class="nav-item {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
                </a>

                <a href="#" class="nav-item">
                    <i class="fas fa-user-circle"></i>
                    <span>My Account</span>
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

        <div class="main-content">
            <header class="top-header">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="header-search">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Search society, flat, member, invoice...">
                </div>

                <div class="header-actions">
                    <div class="notification-dropdown">
                        <button class="icon-btn">
                            <i class="far fa-bell"></i>
                            <span class="notification-badge">12</span>
                        </button>
                    </div>

                    <div class="profile-dropdown">
                        <button class="profile-btn">
                            <div class="avatar">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=2563EB&color=fff" alt="">
                            </div>
                            <div class="profile-info">
                                <span class="profile-name">{{ auth()->user()->name ?? 'Administrator' }}</span>
                                <span class="profile-role">Super Admin</span>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>
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
