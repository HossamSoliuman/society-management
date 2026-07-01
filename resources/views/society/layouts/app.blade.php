<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Society Management</title>
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
                    <span class="brand-subtitle">Management System</span>
                </div>
            </div>

            <div class="sidebar-label">SUPER ADMIN</div>

            <nav class="sidebar-nav">
                {{-- Dashboard --}}
                <a href="{{ route('society.dashboard') }}" class="nav-item {{ request()->routeIs('society.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>

                {{-- Society Profile --}}
                <a href="{{ route('society.profile') }}" class="nav-item {{ request()->routeIs('society.profile*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span>Society Profile</span>
                </a>

                {{-- Member Management (group) --}}
                <div class="nav-group {{ request()->routeIs('society.members.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-users"></i>
                        <span>Member Management</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.members.index') }}" class="nav-item {{ request()->routeIs('society.members.index') || request()->routeIs('society.members.show') || request()->routeIs('society.members.edit') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            <span>All Members</span>
                        </a>
                        <a href="{{ route('society.members.create') }}" class="nav-item {{ request()->routeIs('society.members.create') ? 'active' : '' }}">
                            <i class="fas fa-user-plus"></i>
                            <span>Add Member</span>
                        </a>
                        <a href="{{ route('society.placeholder', ['page' => 'Member Requests']) }}" class="nav-item {{ request()->routeIs('society.placeholder') && request()->route('page') === 'Member Requests' ? 'active' : '' }}">
                            <i class="fas fa-user-clock"></i>
                            <span>Member Requests</span>
                        </a>
                        <a href="{{ route('society.placeholder', ['page' => 'Family Members']) }}" class="nav-item {{ request()->routeIs('society.placeholder') && request()->route('page') === 'Family Members' ? 'active' : '' }}">
                            <i class="fas fa-people-roof"></i>
                            <span>Family Members</span>
                        </a>
                    </div>
                </div>

                {{-- Unit Management (group) --}}
                <div class="nav-group {{ request()->routeIs('society.units.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-building-user"></i>
                        <span>Unit Management</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.units.index') }}" class="nav-item {{ request()->routeIs('society.units.index') || request()->routeIs('society.units.show') || request()->routeIs('society.units.edit') || request()->routeIs('society.units.create') || request()->routeIs('society.units.import') ? 'active' : '' }}">
                            <i class="fas fa-city"></i>
                            <span>Units</span>
                        </a>
                        <a href="{{ route('society.placeholder', ['page' => 'Unit Owners']) }}" class="nav-item">
                            <i class="fas fa-user-tie"></i>
                            <span>Unit Owners</span>
                        </a>
                        <a href="{{ route('society.placeholder', ['page' => 'Occupancy Status']) }}" class="nav-item">
                            <i class="fas fa-house-circle-check"></i>
                            <span>Occupancy Status</span>
                        </a>
                    </div>
                </div>

                {{-- Tenant Management (group, stub) --}}
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <i class="fas fa-user-group"></i>
                        <span>Tenant Management</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.placeholder', ['page' => 'All Tenants']) }}" class="nav-item"><i class="fas fa-list"></i><span>All Tenants</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Add Tenant']) }}" class="nav-item"><i class="fas fa-user-plus"></i><span>Add Tenant</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Tenant Agreements']) }}" class="nav-item"><i class="fas fa-file-signature"></i><span>Tenant Agreements</span></a>
                    </div>
                </div>

                {{-- Staff Management (group, stub) --}}
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <i class="fas fa-user-tie"></i>
                        <span>Staff Management</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.placeholder', ['page' => 'All Staff']) }}" class="nav-item"><i class="fas fa-list"></i><span>All Staff</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Add Staff']) }}" class="nav-item"><i class="fas fa-user-plus"></i><span>Add Staff</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Attendance']) }}" class="nav-item"><i class="fas fa-calendar-check"></i><span>Attendance</span></a>
                    </div>
                </div>

                {{-- Visitor Management (group, stub) --}}
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <i class="fas fa-id-badge"></i>
                        <span>Visitor Management</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.placeholder', ['page' => 'Visitor Logs']) }}" class="nav-item"><i class="fas fa-list"></i><span>Visitor Logs</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Pre-Approvals']) }}" class="nav-item"><i class="fas fa-user-check"></i><span>Pre-Approvals</span></a>
                    </div>
                </div>

                {{-- Vehicle Management (group, stub) --}}
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <i class="fas fa-car"></i>
                        <span>Vehicle Management</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.placeholder', ['page' => 'All Vehicles']) }}" class="nav-item"><i class="fas fa-list"></i><span>All Vehicles</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Register Vehicle']) }}" class="nav-item"><i class="fas fa-plus"></i><span>Register Vehicle</span></a>
                    </div>
                </div>

                {{-- Parking Management (group, stub) --}}
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <i class="fas fa-square-parking"></i>
                        <span>Parking Management</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.placeholder', ['page' => 'Parking Slots']) }}" class="nav-item"><i class="fas fa-list"></i><span>Parking Slots</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Allotments']) }}" class="nav-item"><i class="fas fa-square-parking"></i><span>Allotments</span></a>
                    </div>
                </div>

                <div class="sidebar-label" style="padding-left: 8px;">FINANCE</div>

                {{-- Maintenance Billing (group) --}}
                <div class="nav-group {{ request()->routeIs('society.billing.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Maintenance Billing</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.placeholder', ['page' => 'Create Bill']) }}" class="nav-item"><i class="fas fa-file-circle-plus"></i><span>Create Bill</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Bill List']) }}" class="nav-item"><i class="fas fa-list"></i><span>Bill List</span></a>
                        <a href="{{ route('society.billing.bulk-upload') }}" class="nav-item {{ request()->routeIs('society.billing.bulk-upload') ? 'active' : '' }}"><i class="fas fa-file-arrow-up"></i><span>Bulk Upload</span></a>
                        <a href="{{ route('society.billing.settings.general') }}" class="nav-item {{ request()->routeIs('society.billing.settings.general') ? 'active' : '' }}"><i class="fas fa-gear"></i><span>Bill Settings</span></a>
                        <a href="{{ route('society.billing.settings.charge-heads') }}" class="nav-item {{ request()->routeIs('society.billing.settings.charge-heads') ? 'active' : '' }}"><i class="fas fa-list-check"></i><span>Charge Heads</span></a>
                        <a href="{{ route('society.billing.settings.design') }}" class="nav-item {{ request()->routeIs('society.billing.settings.design') ? 'active' : '' }}"><i class="fas fa-palette"></i><span>Bill Design</span></a>
                        <a href="{{ route('society.billing.settings.late-fee') }}" class="nav-item {{ request()->routeIs('society.billing.settings.late-fee') ? 'active' : '' }}"><i class="fas fa-percent"></i><span>Late Fees &amp; Penalty</span></a>
                        <a href="{{ route('society.billing.settings.taxes') }}" class="nav-item {{ request()->routeIs('society.billing.settings.taxes') ? 'active' : '' }}"><i class="fas fa-receipt"></i><span>Taxes</span></a>
                        <a href="{{ route('society.billing.settings.numbering') }}" class="nav-item {{ request()->routeIs('society.billing.settings.numbering') ? 'active' : '' }}"><i class="fas fa-hashtag"></i><span>Numbering</span></a>
                    </div>
                </div>

                {{-- Collections (group) --}}
                <div class="nav-group {{ request()->routeIs('society.collections.*') ? 'open' : '' }}">
                    <button class="nav-group-toggle">
                        <i class="fas fa-hand-holding-dollar"></i>
                        <span>Collections</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.placeholder', ['page' => 'Payment Collection']) }}" class="nav-item"><i class="fas fa-money-bill-wave"></i><span>Payment Collection</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Online Payments']) }}" class="nav-item"><i class="fas fa-credit-card"></i><span>Online Payments</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Pending Dues']) }}" class="nav-item"><i class="fas fa-clock"></i><span>Pending Dues</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Payment Receipts']) }}" class="nav-item"><i class="fas fa-file-invoice"></i><span>Payment Receipts</span></a>
                    </div>
                </div>

                {{-- Expenses --}}
                <a href="{{ route('society.placeholder', ['page' => 'Expenses']) }}" class="nav-item">
                    <i class="fas fa-receipt"></i>
                    <span>Expenses</span>
                </a>

                {{-- Accounting --}}
                <a href="{{ route('society.placeholder', ['page' => 'Accounting']) }}" class="nav-item">
                    <i class="fas fa-calculator"></i>
                    <span>Accounting</span>
                </a>

                {{-- Reports (group, stub) --}}
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <i class="fas fa-chart-column"></i>
                        <span>Reports</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.placeholder', ['page' => 'Collection Report']) }}" class="nav-item"><i class="fas fa-chart-pie"></i><span>Collection Report</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Expense Report']) }}" class="nav-item"><i class="fas fa-chart-line"></i><span>Expense Report</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Defaulter Report']) }}" class="nav-item"><i class="fas fa-triangle-exclamation"></i><span>Defaulter Report</span></a>
                    </div>
                </div>

                <div class="sidebar-label" style="padding-left: 8px;">OTHER</div>

                <a href="{{ route('society.placeholder', ['page' => 'Amenities Booking']) }}" class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Amenities Booking</span>
                </a>
                <a href="{{ route('society.placeholder', ['page' => 'Complaint Management']) }}" class="nav-item">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>Complaint Management</span>
                </a>
                <a href="{{ route('society.placeholder', ['page' => 'Documents']) }}" class="nav-item">
                    <i class="fas fa-folder-open"></i>
                    <span>Documents</span>
                </a>
                <a href="{{ route('society.placeholder', ['page' => 'Notifications']) }}" class="nav-item">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
                <a href="{{ route('society.placeholder', ['page' => 'Support Tickets']) }}" class="nav-item">
                    <i class="fas fa-headset"></i>
                    <span>Support Tickets</span>
                </a>
                <a href="{{ route('society.placeholder', ['page' => 'Activity Logs']) }}" class="nav-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Activity Logs</span>
                </a>

                {{-- System Settings (group, stub) --}}
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <i class="fas fa-gear"></i>
                        <span>System Settings</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div class="nav-submenu">
                        <a href="{{ route('society.placeholder', ['page' => 'General Settings']) }}" class="nav-item"><i class="fas fa-sliders"></i><span>General Settings</span></a>
                        <a href="{{ route('society.placeholder', ['page' => 'Roles & Permissions']) }}" class="nav-item"><i class="fas fa-user-shield"></i><span>Roles &amp; Permissions</span></a>
                    </div>
                </div>
            </nav>

            {{-- Need Help? mini-card --}}
            <div style="padding: 12px;">
                <div style="background: var(--primary-light); border-radius: var(--radius-md); padding: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                        <i class="fas fa-headset" style="color: var(--primary); font-size: 16px;"></i>
                        <span style="font-size: 13px; font-weight: 700; color: var(--text-primary);">Need Help?</span>
                    </div>
                    <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 12px;">We're here to help you</div>
                    <a href="{{ route('society.placeholder', ['page' => 'Support Tickets']) }}" class="btn btn-primary btn-sm" style="width: 100%;">Raise Support Ticket</a>
                </div>
            </div>

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
                    <input type="text" placeholder="Search member, flat, invoice, complaint...">
                </div>

                <div class="header-actions">
                    <div class="notification-dropdown">
                        <button class="icon-btn">
                            <i class="far fa-bell"></i>
                            <span class="notification-badge">12</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="dropdown-menu-header">Notifications</div>
                            <a href="{{ route('society.placeholder', ['page' => 'Notifications']) }}" class="dropdown-menu-item">
                                <i class="fas fa-bullhorn"></i>
                                <span>View all notifications</span>
                            </a>
                            <a href="{{ route('society.placeholder', ['page' => 'Complaint Management']) }}" class="dropdown-menu-item">
                                <i class="fas fa-triangle-exclamation"></i>
                                <span>Open complaints</span>
                            </a>
                        </div>
                    </div>

                    <div class="profile-dropdown">
                        <button class="profile-btn">
                            <div class="avatar">
                                <img src="https://ui-avatars.com/api/?name=SA&background=E84B1E&color=fff" alt="">
                            </div>
                            <div class="profile-info">
                                <span class="profile-name">Super Admin</span>
                                <span class="profile-role">Super Admin</span>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="{{ route('society.profile') }}" class="dropdown-menu-item">
                                <i class="fas fa-user"></i>
                                <span>Profile</span>
                            </a>
                            <a href="{{ route('society.placeholder', ['page' => 'System Settings']) }}" class="dropdown-menu-item">
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
