<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PM Scheduling') - Preventive Maintenance</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    {{-- FullCalendar CSS (untuk halaman kalender) --}}
    @stack('styles')

    <style>
        :root {
            --sidebar-width: 270px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #3b82f6;
            --body-bg: #f1f5f9;
            --card-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --card-shadow-lg: 0 10px 25px rgba(0, 0, 0, .08);
            --gradient-primary: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --gradient-info: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--body-bg);
            min-height: 100vh;
        }

        /* ======== SIDEBAR ======== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1050;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .sidebar-brand h4 {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }

        .sidebar-brand small {
            color: rgba(255, 255, 255, .5);
            font-size: .75rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-heading {
            padding: .5rem 1.25rem;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, .35);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: .65rem 1.25rem;
            color: rgba(255, 255, 255, .6);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            transition: all .2s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            color: #fff;
            background: var(--sidebar-hover);
        }

        .sidebar-link.active {
            color: #fff;
            background: rgba(59, 130, 246, .15);
            border-left-color: var(--sidebar-active);
        }

        .sidebar-link i {
            width: 20px;
            margin-right: .75rem;
            font-size: 1.1rem;
            text-align: center;
        }

        /* ======== MAIN CONTENT ======== */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* ======== TOP NAVBAR ======== */
        .top-navbar {
            background: #fff;
            padding: .75rem 1.5rem;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1040;
        }

        .top-navbar .page-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .top-navbar .user-info {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
        }

        /* ======== CONTENT AREA ======== */
        .content-area {
            padding: 1.5rem;
        }

        /* ======== STAT CARDS ======== */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: var(--card-shadow);
            transition: transform .2s ease, box-shadow .2s ease;
            border: 1px solid rgba(0, 0, 0, .04);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow-lg);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: .8rem;
            color: #64748b;
            font-weight: 500;
        }

        /* ======== CUSTOM CARDS ======== */
        .card-custom {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .card-custom .card-header-custom {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-custom .card-header-custom h6 {
            font-weight: 700;
            font-size: .95rem;
            color: #1e293b;
            margin: 0;
        }

        .card-custom .card-body-custom {
            padding: 1.25rem;
        }

        /* ======== TABLES ======== */
        .table-custom {
            margin: 0;
        }

        .table-custom thead th {
            background: #f8fafc;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            padding: .75rem 1rem;
        }

        .table-custom tbody td {
            padding: .75rem 1rem;
            font-size: .875rem;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-custom tbody tr:hover {
            background: #f8fafc;
        }

        /* ======== BADGES ======== */
        .badge-status {
            padding: .35em .7em;
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 600;
        }

        /* ======== BUTTONS ======== */
        .btn-gradient-primary {
            background: var(--gradient-primary);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            transition: all .2s ease;
        }

        .btn-gradient-primary:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, .35);
        }

        /* ======== FORM INPUTS ======== */
        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 .2rem rgba(59, 130, 246, .15);
        }

        /* ======== RESPONSIVE ======== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        /* ======== ANIMATION ======== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp .4s ease forwards;
        }

        .animate-in:nth-child(2) {
            animation-delay: .05s;
        }

        .animate-in:nth-child(3) {
            animation-delay: .1s;
        }

        .animate-in:nth-child(4) {
            animation-delay: .15s;
        }

        .animate-in:nth-child(5) {
            animation-delay: .2s;
        }
    </style>
</head>

<body>
    {{-- ======== SIDEBAR ======== --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h4><i class="bi bi-gear-wide-connected me-2"></i>PM System</h4>
            <small>Preventive Maintenance</small>
        </div>

        <nav class="sidebar-nav">
            @if (auth()->user()->isAdmin())
                <div class="sidebar-heading">Main Menu</div>
                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                <div class="sidebar-heading mt-3">Data Master</div>
                <a href="{{ route('admin.machines.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.machines.*') ? 'active' : '' }}">
                    <i class="bi bi-cpu"></i> Mesin
                </a>
                <a href="{{ route('admin.users.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Kelola Teknisi
                </a>
                <a href="{{ route('admin.jenis-kerusakan.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.jenis-kerusakan.*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i> Jenis Kerusakan
                </a>

                <div class="sidebar-heading mt-3">Maintenance</div>
                <a href="{{ route('admin.maintenance.history') }}"
                    class="sidebar-link {{ request()->routeIs('admin.maintenance.history*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Data Historis
                </a>
                <a href="{{ route('admin.schedules.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Jadwal
                </a>
            @else
                <div class="sidebar-heading">Main Menu</div>
                <a href="{{ route('teknisi.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('teknisi.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                <div class="sidebar-heading mt-3">Pekerjaan</div>
                <a href="{{ route('teknisi.schedules') }}"
                    class="sidebar-link {{ request()->routeIs('teknisi.schedules*') ? 'active' : '' }}">
                    <i class="bi bi-list-check"></i> Jadwal Saya
                </a>
            @endif
        </nav>
    </aside>

    {{-- ======== MAIN CONTENT ======== --}}
    <div class="main-content">
        {{-- Top Navbar --}}
        <div class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-md-none"
                    onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="page-title">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="user-info">
                <div>
                    <div class="fw-semibold" style="font-size:.85rem">{{ auth()->user()->name }}</div>
                    <div class="text-muted" style="font-size:.7rem">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Content Area --}}
        <div class="content-area">
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
