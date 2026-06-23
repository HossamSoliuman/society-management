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
                {{-- Dashboard --}}
                <a href="{{ route('superadmin.dashboard') }}" class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>

                {{-- Society Management --}}
                <a href="{{ route('superadmin.societies.index') }}" class="nav-item {{ request()->routeIs('superadmin.societies.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span>Society Management</span>
                </a>

                {{-- Subscription Management (group) --}}
                <div class="nav-group {{ request()->routeIs('superadmin.subscription.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-credit-card"></i>
                        <span>Subscription Management</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('superadmin.subscription.plans') }}" class="nav-item {{ request()->routeIs('superadmin.subscription.plans*') ? 'active' : '' }}">
                            <i class="fas fa-layer-group"></i>
                            <span>Plans</span>
                        </a>
                        <a href="{{ route('superadmin.subscription.subscriptions') }}" class="nav-item {{ request()->routeIs('superadmin.subscription.subscriptions*') ? 'active' : '' }}">
                            <i class="fas fa-list-check"></i>
                            <span>Subscriptions</span>
                        </a>
                        <a href="{{ route('superadmin.subscription.renewals') }}" class="nav-item {{ request()->routeIs('superadmin.subscription.renewals') ? 'active' : '' }}">
                            <i class="fas fa-rotate"></i>
                            <span>Renewals</span>
                        </a>
                    </div>
                </div>

                {{-- Revenue & Billing (group) --}}
                <div class="nav-group {{ request()->routeIs('superadmin.billing.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-indian-rupee-sign"></i>
                        <span>Revenue &amp; Billing</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('superadmin.billing.overview') }}" class="nav-item {{ request()->routeIs('superadmin.billing.overview') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i>
                            <span>Overview</span>
                        </a>
                        <a href="{{ route('superadmin.billing.invoices') }}" class="nav-item {{ request()->routeIs('superadmin.billing.invoices') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice"></i>
                            <span>Invoices</span>
                        </a>
                        <a href="{{ route('superadmin.billing.payments') }}" class="nav-item {{ request()->routeIs('superadmin.billing.payments') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Payments</span>
                        </a>
                        <a href="{{ route('superadmin.billing.receipts') }}" class="nav-item {{ request()->routeIs('superadmin.billing.receipts') ? 'active' : '' }}">
                            <i class="fas fa-receipt"></i>
                            <span>Receipts</span>
                        </a>
                        <a href="{{ route('superadmin.billing.outstanding') }}" class="nav-item {{ request()->routeIs('superadmin.billing.outstanding') ? 'active' : '' }}">
                            <i class="fas fa-clock"></i>
                            <span>Outstanding</span>
                        </a>
                        <a href="{{ route('superadmin.billing.overdue') }}" class="nav-item {{ request()->routeIs('superadmin.billing.overdue') ? 'active' : '' }}">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Overdue</span>
                        </a>
                        <a href="{{ route('superadmin.billing.refunds') }}" class="nav-item {{ request()->routeIs('superadmin.billing.refunds') ? 'active' : '' }}">
                            <i class="fas fa-undo"></i>
                            <span>Refunds</span>
                        </a>
                    </div>
                </div>

                {{-- User Management (group) --}}
                <div class="nav-group {{ request()->routeIs('superadmin.users.*') || request()->routeIs('superadmin.roles.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('superadmin.users.index') }}" class="nav-item {{ request()->routeIs('superadmin.users.index') || request()->routeIs('superadmin.users.create') || request()->routeIs('superadmin.users.show', []) || request()->routeIs('superadmin.users.edit', []) ? 'active' : '' }}">
                            <i class="fas fa-user-shield"></i>
                            <span>Society Admin Users</span>
                        </a>
                        <a href="{{ route('superadmin.roles.index') }}" class="nav-item {{ request()->routeIs('superadmin.roles.*') ? 'active' : '' }}">
                            <i class="fas fa-user-tag"></i>
                            <span>Role Management</span>
                        </a>
                        <a href="{{ route('superadmin.users.login-activity') }}" class="nav-item {{ request()->routeIs('superadmin.users.login-activity') ? 'active' : '' }}">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login Activity</span>
                        </a>
                    </div>
                </div>

                {{-- Reports (group) --}}
                <div class="nav-group {{ request()->routeIs('superadmin.reports.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('superadmin.reports.index') }}" class="nav-item {{ request()->routeIs('superadmin.reports.index') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            <span>All Reports</span>
                        </a>
                        <a href="{{ route('superadmin.reports.society') }}" class="nav-item {{ request()->routeIs('superadmin.reports.society') ? 'active' : '' }}">
                            <i class="fas fa-building"></i>
                            <span>Society Report</span>
                        </a>
                        <a href="{{ route('superadmin.reports.revenue') }}" class="nav-item {{ request()->routeIs('superadmin.reports.revenue') ? 'active' : '' }}">
                            <i class="fas fa-rupee-sign"></i>
                            <span>Revenue Report</span>
                        </a>
                        <a href="{{ route('superadmin.reports.subscription') }}" class="nav-item {{ request()->routeIs('superadmin.reports.subscription') ? 'active' : '' }}">
                            <i class="fas fa-credit-card"></i>
                            <span>Subscription Report</span>
                        </a>
                        <a href="{{ route('superadmin.reports.payment') }}" class="nav-item {{ request()->routeIs('superadmin.reports.payment') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Payment Report</span>
                        </a>
                    </div>
                </div>

                {{-- Notifications (group) --}}
                <div class="nav-group {{ request()->routeIs('superadmin.notification.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('superadmin.notification.announcements.create') }}" class="nav-item {{ request()->routeIs('superadmin.notification.announcements.create') ? 'active' : '' }}">
                            <i class="fas fa-bullhorn"></i>
                            <span>Send Announcement</span>
                        </a>
                        <a href="{{ route('superadmin.notification.announcements') }}" class="nav-item {{ request()->routeIs('superadmin.notification.announcements') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            <span>Announcements</span>
                        </a>
                        <a href="{{ route('superadmin.notification.renewals') }}" class="nav-item {{ request()->routeIs('superadmin.notification.renewals') ? 'active' : '' }}">
                            <i class="fas fa-rotate"></i>
                            <span>Renewal Alerts</span>
                        </a>
                    </div>
                </div>

                {{-- Support Tickets --}}
                <a href="{{ route('superadmin.tickets.index') }}" class="nav-item {{ request()->routeIs('superadmin.tickets.*') ? 'active' : '' }}">
                    <i class="fas fa-headset"></i>
                    <span>Support Tickets</span>
                </a>

                {{-- Activity Logs (group) --}}
                <div class="nav-group {{ request()->routeIs('superadmin.logs.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Activity Logs</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('superadmin.logs.user-activities') }}" class="nav-item {{ request()->routeIs('superadmin.logs.user-activities') ? 'active' : '' }}">
                            <i class="fas fa-user-clock"></i>
                            <span>User Activities</span>
                        </a>
                        <a href="{{ route('superadmin.logs.system-logs') }}" class="nav-item {{ request()->routeIs('superadmin.logs.system-logs') ? 'active' : '' }}">
                            <i class="fas fa-server"></i>
                            <span>System Logs</span>
                        </a>
                        <a href="{{ route('superadmin.logs.audit-trail') }}" class="nav-item {{ request()->routeIs('superadmin.logs.audit-trail') ? 'active' : '' }}">
                            <i class="fas fa-shield-halved"></i>
                            <span>Audit Trail</span>
                        </a>
                    </div>
                </div>

                {{-- Masters --}}
                <a href="{{ route('superadmin.masters.index') }}" class="nav-item {{ request()->routeIs('superadmin.masters.*') ? 'active' : '' }}">
                    <i class="fas fa-database"></i>
                    <span>Masters</span>
                </a>

                {{-- Terms & Conditions --}}
                <a href="{{ route('superadmin.terms.index') }}" class="nav-item {{ request()->routeIs('superadmin.terms.*') ? 'active' : '' }}">
                    <i class="fas fa-file-contract"></i>
                    <span>Terms &amp; Conditions</span>
                </a>

                {{-- System Settings (group) --}}
                <div class="nav-group {{ request()->routeIs('superadmin.settings.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-cog"></i>
                        <span>System Settings</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('superadmin.settings.company-profile') }}" class="nav-item {{ request()->routeIs('superadmin.settings.company-profile') ? 'active' : '' }}">
                            <i class="fas fa-building-columns"></i>
                            <span>Company Profile</span>
                        </a>
                        <a href="{{ route('superadmin.settings.prefix') }}" class="nav-item {{ request()->routeIs('superadmin.settings.prefix') ? 'active' : '' }}">
                            <i class="fas fa-tag"></i>
                            <span>Prefix Settings</span>
                        </a>
                        <a href="{{ route('superadmin.settings.smtp') }}" class="nav-item {{ request()->routeIs('superadmin.settings.smtp') ? 'active' : '' }}">
                            <i class="fas fa-envelope-open-text"></i>
                            <span>SMTP Settings</span>
                        </a>
                        <a href="{{ route('superadmin.settings.backup') }}" class="nav-item {{ request()->routeIs('superadmin.settings.backup') ? 'active' : '' }}">
                            <i class="fas fa-hard-drive"></i>
                            <span>Backup Settings</span>
                        </a>
                        <a href="{{ route('superadmin.settings.security') }}" class="nav-item {{ request()->routeIs('superadmin.settings.security') ? 'active' : '' }}">
                            <i class="fas fa-lock"></i>
                            <span>Security Settings</span>
                        </a>
                    </div>
                </div>

                {{-- My Account --}}
                <a href="{{ route('superadmin.account.index') }}" class="nav-item {{ request()->routeIs('superadmin.account.*') ? 'active' : '' }}">
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
                        <div class="dropdown-menu">
                            <div class="dropdown-menu-header">Notifications</div>
                            <a href="{{ route('superadmin.notification.announcements') }}" class="dropdown-menu-item">
                                <i class="fas fa-bullhorn"></i>
                                <span>View all announcements</span>
                            </a>
                            <a href="{{ route('superadmin.notification.renewals') }}" class="dropdown-menu-item">
                                <i class="fas fa-rotate"></i>
                                <span>Renewal alerts</span>
                            </a>
                        </div>
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
                        <div class="dropdown-menu">
                            <a href="{{ route('superadmin.account.index') }}" class="dropdown-menu-item">
                                <i class="fas fa-user"></i>
                                <span>Profile</span>
                            </a>
                            <a href="{{ route('superadmin.settings.company-profile') }}" class="dropdown-menu-item">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
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
