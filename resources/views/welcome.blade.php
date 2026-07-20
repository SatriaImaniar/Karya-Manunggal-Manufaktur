<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Sistem Penjadwalan Preventive Maintenance berbasis Time-Based Maintenance (TBM) untuk PT. Karya Manunggal Manufaktur. Kelola MTBF, MTTR, dan jadwal perawatan mesin secara efisien.">
    <meta name="keywords" content="preventive maintenance, TBM, MTBF, MTTR, jadwal maintenance, mesin manufaktur">
    <title>Sistem Preventive Maintenance (TBM) — PT. Karya Manunggal Manufaktur</title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1E3A8A">

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="{{ asset('zani.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('zani.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('zani.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: hsl(221, 83%, 58%);
            --primary-glow: hsl(221, 83%, 68%);
            --accent: hsl(172, 66%, 50%);
            --accent-warm: hsl(38, 92%, 60%);
            --surface-1: hsl(222, 47%, 8%);
            --surface-2: hsl(222, 40%, 12%);
            --surface-3: hsl(222, 32%, 17%);
            --border: hsla(221, 50%, 60%, 0.15);
            --text-primary: hsl(210, 40%, 96%);
            --text-secondary: hsl(215, 20%, 65%);
            --text-muted: hsl(215, 15%, 45%);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--surface-1);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ─── CANVAS BACKGROUND ─── */
        #bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        /* ─── GRADIENT MESH ─── */
        .bg-mesh {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, hsla(221, 83%, 30%, 0.35) 0%, transparent 65%),
                radial-gradient(ellipse 60% 50% at 85% 80%, hsla(172, 66%, 25%, 0.25) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 60% 40%, hsla(38, 92%, 35%, 0.12) 0%, transparent 55%);
        }

        /* ─── MAIN WRAPPER ─── */
        .page-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── NAVBAR ─── */
        .navbar {
            padding: 1.25rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
            background: hsla(222, 47%, 8%, 0.7);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
            flex-shrink: 0;
        }

        .brand-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 0.65rem;
            font-weight: 400;
            color: var(--text-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1.5px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-nav:hover {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 0 20px hsla(221, 83%, 58%, 0.4);
        }

        .btn-nav.filled {
            background: var(--primary);
            color: #fff;
        }

        .btn-nav.filled:hover {
            background: var(--primary-glow);
            box-shadow: 0 0 24px hsla(221, 83%, 68%, 0.5);
        }

        /* ─── HERO ─── */
        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5rem 1.5rem 3rem;
            text-align: center;
        }

        .hero-inner {
            max-width: 760px;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 1rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            background: hsla(172, 66%, 50%, 0.12);
            border: 1px solid hsla(172, 66%, 50%, 0.3);
            color: var(--accent);
            margin-bottom: 1.75rem;
            text-transform: uppercase;
        }

        .badge-pill i {
            font-size: 0.8rem;
        }

        .hero-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(2.25rem, 5vw, 3.75rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .hero-title .highlight {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-desc {
            font-size: clamp(1rem, 2vw, 1.15rem);
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 2.5rem;
            max-width: 580px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ─── CTA BUTTONS ─── */
        .cta-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 3.5rem;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.85rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-cta::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), transparent);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn-cta:hover::before {
            opacity: 1;
        }

        .btn-cta-primary {
            background: linear-gradient(135deg, var(--primary), hsl(221, 83%, 48%));
            color: #fff;
            box-shadow: 0 4px 24px hsla(221, 83%, 58%, 0.35), 0 0 0 1px hsla(221, 83%, 58%, 0.2);
        }

        .btn-cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px hsla(221, 83%, 58%, 0.5), 0 0 0 1px hsla(221, 83%, 58%, 0.3);
            color: #fff;
        }

        .btn-cta-secondary {
            background: hsla(215, 30%, 18%, 0.6);
            color: var(--text-secondary);
            border: 1.5px solid var(--border);
            backdrop-filter: blur(8px);
        }

        .btn-cta-secondary:hover {
            border-color: hsla(221, 50%, 60%, 0.4);
            color: var(--text-primary);
            transform: translateY(-2px);
            background: hsla(215, 30%, 22%, 0.7);
        }

        /* ─── STATS ROW ─── */
        .stats-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2.5rem;
            flex-wrap: wrap;
            padding: 1.5rem 2rem;
            background: hsla(222, 40%, 12%, 0.6);
            border: 1px solid var(--border);
            border-radius: 16px;
            backdrop-filter: blur(8px);
        }

        .stat-item {
            text-align: center;
        }

        .stat-num {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-num span {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 500;
        }

        .stat-divider {
            width: 1px;
            height: 40px;
            background: var(--border);
        }

        /* ─── FEATURES SECTION ─── */
        .section {
            padding: 5rem 1.5rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3.5rem;
        }

        .section-tag {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 0.75rem;
        }

        .section-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(1.75rem, 3.5vw, 2.5rem);
            font-weight: 800;
            letter-spacing: -0.025em;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .section-desc {
            font-size: 1rem;
            color: var(--text-secondary);
            max-width: 540px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ─── FEATURE CARDS ─── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .feature-card {
            background: hsla(222, 40%, 11%, 0.7);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--card-color, var(--primary)), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: hsla(221, 50%, 60%, 0.3);
            box-shadow: 0 20px 40px hsla(222, 47%, 5%, 0.5);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1.25rem;
        }

        .feature-card h3 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.6rem;
        }

        .feature-card p {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.65;
        }

        /* Card color variants */
        .fc-blue {
            --card-color: var(--primary);
            background: linear-gradient(135deg, var(--primary), hsl(221, 83%, 40%));
        }

        .fc-teal {
            --card-color: var(--accent);
            background: linear-gradient(135deg, var(--accent), hsl(172, 66%, 35%));
        }

        .fc-amber {
            --card-color: var(--accent-warm);
            background: linear-gradient(135deg, var(--accent-warm), hsl(38, 92%, 42%));
        }

        .fc-violet {
            --card-color: hsl(260, 70%, 60%);
            background: linear-gradient(135deg, hsl(260, 70%, 60%), hsl(260, 70%, 40%));
        }

        .fc-rose {
            --card-color: hsl(345, 80%, 62%);
            background: linear-gradient(135deg, hsl(345, 80%, 62%), hsl(345, 80%, 42%));
        }

        .fc-green {
            --card-color: hsl(145, 60%, 50%);
            background: linear-gradient(135deg, hsl(145, 60%, 50%), hsl(145, 60%, 35%));
        }

        .fc-blue,
        .fc-teal,
        .fc-amber,
        .fc-violet,
        .fc-rose,
        .fc-green {
            color: rgba(255, 255, 255, 0.9);
        }

        /* ─── HOW IT WORKS ─── */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            max-width: 960px;
            margin: 0 auto;
            position: relative;
        }

        .steps-grid::before {
            content: '';
            position: absolute;
            top: 28px;
            left: calc(1fr / 2);
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), var(--border), transparent);
            display: none;
        }

        .step-card {
            background: hsla(222, 40%, 12%, 0.5);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.75rem 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .step-card:hover {
            border-color: hsla(221, 83%, 58%, 0.35);
            transform: translateY(-3px);
        }

        .step-number {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 16px hsla(221, 83%, 58%, 0.35);
        }

        .step-card h4 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .step-card p {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* ─── CTA BANNER ─── */
        .cta-banner {
            background: linear-gradient(135deg,
                    hsla(221, 83%, 20%, 0.8),
                    hsla(172, 66%, 20%, 0.6));
            border: 1px solid hsla(221, 83%, 58%, 0.2);
            border-radius: 24px;
            padding: 4rem 2rem;
            text-align: center;
            max-width: 760px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .cta-banner::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, hsla(172, 66%, 50%, 0.15), transparent 70%);
            border-radius: 50%;
        }

        .cta-banner::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, hsla(221, 83%, 58%, 0.12), transparent 70%);
            border-radius: 50%;
        }

        .cta-banner h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(1.5rem, 3vw, 2.25rem);
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .cta-banner p {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .cta-banner .cta-group {
            position: relative;
            z-index: 1;
            margin-bottom: 0;
        }

        /* ─── FOOTER ─── */
        footer {
            padding: 2rem 1.5rem;
            border-top: 1px solid var(--border);
            text-align: center;
        }

        .footer-inner {
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .footer-copy {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .footer-links {
            display: flex;
            gap: 1.25rem;
        }

        .footer-links a {
            font-size: 0.78rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--text-secondary);
        }

        /* ─── SECTION DIVIDER ─── */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
            max-width: 900px;
            margin: 0 auto;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 640px) {
            .navbar {
                padding: 1rem;
            }

            .stat-divider {
                display: none;
            }

            .stats-row {
                gap: 1.25rem;
            }

            .footer-inner {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }

        /* ─── ANIMATIONS ─── */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 4px 24px hsla(221, 83%, 58%, 0.35);
            }

            50% {
                box-shadow: 0 4px 36px hsla(221, 83%, 58%, 0.6);
            }
        }

        .animate-fade-up {
            opacity: 0;
            animation: fadeUp 0.7s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }

        .delay-5 {
            animation-delay: 0.5s;
        }

        .delay-6 {
            animation-delay: 0.6s;
        }

        .btn-cta-primary {
            animation: pulse-glow 3s ease-in-out infinite;
        }
    </style>
</head>

<body>

    <!-- Background Effects -->
    <div class="bg-mesh"></div>
    <canvas id="bg-canvas"></canvas>

    <div class="page-wrapper">

        <!-- ═══════════════════ NAVBAR ═══════════════════ -->
        <nav class="navbar">
            <a href="{{ route('home') }}" class="navbar-brand">
                <div class="brand-icon">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
                <div>
                    <div class="brand-name">KMM Maintenance</div>
                    <div class="brand-sub">PT. Karya Manunggal Manufaktur</div>
                </div>
            </a>

            <div>
                @auth
                    @php
                        $dashRoute =
                            auth()->user()->role === 'admin' ? route('admin.dashboard') : route('teknisi.dashboard');
                    @endphp
                    <a href="{{ $dashRoute }}" class="btn-nav filled" id="nav-cta-btn">
                        <i class="bi bi-grid-1x2-fill"></i>
                        Ke Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-nav" id="nav-cta-btn">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Masuk
                    </a>
                @endauth
            </div>
        </nav>

        <!-- ═══════════════════ HERO ═══════════════════ -->
        <section class="hero">
            <div class="hero-inner">

                <div class="badge-pill animate-fade-up delay-1">
                    <i class="bi bi-cpu-fill"></i>
                    Time-Based Maintenance (TBM)
                </div>

                <h1 class="hero-title animate-fade-up delay-2">
                    Sistem Penjadwalan<br>
                    <span class="highlight">Preventive Maintenance</span><br>
                    Mesin Manufaktur
                </h1>

                <p class="hero-desc animate-fade-up delay-3">
                    Platform terpadu untuk mengelola jadwal perawatan mesin secara proaktif menggunakan
                    metode <strong style="color: var(--text-primary);">MTBF, MTTR, dan Availability</strong>.
                    Cegah downtime sebelum terjadi, tingkatkan efisiensi produksi PT. Karya Manunggal Manufaktur.
                </p>

                <div class="cta-group animate-fade-up delay-4">
                    @auth
                        @php
                            $dashRoute =
                                auth()->user()->role === 'admin'
                                    ? route('admin.dashboard')
                                    : route('teknisi.dashboard');
                            $dashLabel = auth()->user()->role === 'admin' ? 'Dashboard Admin' : 'Dashboard Teknisi';
                        @endphp
                        <a href="{{ $dashRoute }}" class="btn-cta btn-cta-primary" id="hero-cta-btn">
                            <i class="bi bi-grid-1x2-fill"></i>
                            {{ $dashLabel }}
                        </a>
                        <a href="{{ route('logout') }}" class="btn-cta btn-cta-secondary"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            Keluar
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                            @csrf
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn-cta btn-cta-primary" id="hero-cta-btn">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Login ke Sistem
                        </a>
                        <a href="#features" class="btn-cta btn-cta-secondary">
                            <i class="bi bi-info-circle"></i>
                            Pelajari Lebih Lanjut
                        </a>
                    @endauth
                </div>

                <!-- Stats Row -->
                <div class="stats-row animate-fade-up delay-5">
                    <div class="stat-item">
                        <div class="stat-num"><span>MTBF</span></div>
                        <div class="stat-label">Mean Time Between Failure</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-num"><span>MTTR</span></div>
                        <div class="stat-label">Mean Time To Repair</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-num"><span>Tpm</span></div>
                        <div class="stat-label">Interval Preventive Maintenance</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-num"><span>≥95%</span></div>
                        <div class="stat-label">Target Availability Mesin</div>
                    </div>
                </div>

            </div>
        </section>

        <div class="section-divider"></div>

        <!-- ═══════════════════ FEATURES ═══════════════════ -->
        <section class="section" id="features">
            <div class="section-header">
                <div class="section-tag">Fitur Utama</div>
                <h2 class="section-title">Semua yang Dibutuhkan<br>untuk Maintenance Modern</h2>
                <p class="section-desc">
                    Dari kalkulasi otomatis hingga penjadwalan cerdas, sistem kami menyederhanakan
                    seluruh alur kerja perawatan mesin Anda.
                </p>
            </div>

            <div class="features-grid">

                <div class="feature-card" style="--card-color: var(--primary);">
                    <div class="feature-icon fc-blue">
                        <i class="bi bi-calculator-fill" style="color:#fff;"></i>
                    </div>
                    <h3>Kalkulasi TBM Otomatis</h3>
                    <p>Hitung MTBF, MTTR, Availability, dan interval Tpm secara otomatis dari data historis kerusakan
                        mesin tanpa perlu spreadsheet manual.</p>
                </div>

                <div class="feature-card" style="--card-color: var(--accent);">
                    <div class="feature-icon fc-teal">
                        <i class="bi bi-calendar2-check-fill" style="color:#fff;"></i>
                    </div>
                    <h3>Penjadwalan Preventive</h3>
                    <p>Generate jadwal maintenance preventif secara otomatis berdasarkan nilai Tpm yang dikalkulasi dari
                        setiap histori kerusakan mesin.</p>
                </div>

                <div class="feature-card" style="--card-color: var(--accent-warm);">
                    <div class="feature-icon fc-amber">
                        <i class="bi bi-bar-chart-fill" style="color:#fff;"></i>
                    </div>
                    <h3>Rekap & Analitik Data</h3>
                    <p>Lihat tren availability, perbandingan MTBF antar periode, dan ekspor seluruh data rekap ke format
                        yang siap digunakan untuk laporan.</p>
                </div>

                <div class="feature-card" style="--card-color: hsl(260,70%,60%);">
                    <div class="feature-icon fc-violet">
                        <i class="bi bi-people-fill" style="color:#fff;"></i>
                    </div>
                    <h3>Manajemen Teknisi</h3>
                    <p>Assign teknisi ke jadwal maintenance spesifik. Teknisi dapat memperbarui status pekerjaan secara
                        real-time langsung dari dashboard mereka.</p>
                </div>

                <div class="feature-card" style="--card-color: hsl(345,80%,62%);">
                    <div class="feature-icon fc-rose">
                        <i class="bi bi-exclamation-triangle-fill" style="color:#fff;"></i>
                    </div>
                    <h3>Deteksi Jadwal Overdue</h3>
                    <p>Sistem otomatis menandai jadwal yang terlewat. Notifikasi visual membantu SPV untuk segera
                        mengambil tindakan sebelum mesin bermasalah.</p>
                </div>

                <div class="feature-card" style="--card-color: hsl(145,60%,50%);">
                    <div class="feature-icon fc-green">
                        <i class="bi bi-shield-lock-fill" style="color:#fff;"></i>
                    </div>
                    <h3>Kontrol Akses Berbasis Role</h3>
                    <p>Sistem multi-role (Admin/SPV dan Teknisi) memastikan setiap pengguna hanya dapat mengakses fitur
                        dan data yang sesuai dengan tanggung jawabnya.</p>
                </div>

            </div>
        </section>

        <div class="section-divider"></div>

        <!-- ═══════════════════ HOW IT WORKS ═══════════════════ -->
        <section class="section">
            <div class="section-header">
                <div class="section-tag">Cara Kerja</div>
                <h2 class="section-title">Alur Sistem dalam 4 Langkah</h2>
                <p class="section-desc">
                    Proses sederhana dari input data kerusakan hingga jadwal maintenance siap dieksekusi.
                </p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4>Input Data Historis</h4>
                    <p>Admin memasukkan data waktu operasi, jumlah kerusakan, dan waktu perbaikan untuk setiap mesin per
                        periode.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h4>Kalkulasi Otomatis</h4>
                    <p>Sistem menghitung MTBF, MTTR, Availability, dan menentukan interval Tpm optimal secara otomatis.
                    </p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h4>Generate Jadwal</h4>
                    <p>Jadwal preventive maintenance dibuat otomatis dan assign ke teknisi yang bertanggung jawab.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h4>Eksekusi & Pantau</h4>
                    <p>Teknisi mengeksekusi pekerjaan dan memperbarui status. SPV memantau progress dari dashboard.</p>
                </div>
            </div>
        </section>

        <div class="section-divider"></div>

        <!-- ═══════════════════ BOTTOM CTA ═══════════════════ -->
        <section class="section">
            <div class="cta-banner">
                <h2>Siap Mengelola Maintenance<br>Secara Efisien?</h2>
                <p>Masuk ke sistem dan mulai kelola jadwal preventive maintenance<br>mesin PT. Karya Manunggal
                    Manufaktur sekarang.</p>

                <div class="cta-group">
                    @auth
                        @php
                            $dashRoute =
                                auth()->user()->role === 'admin'
                                    ? route('admin.dashboard')
                                    : route('teknisi.dashboard');
                        @endphp
                        <a href="{{ $dashRoute }}" class="btn-cta btn-cta-primary">
                            <i class="bi bi-grid-1x2-fill"></i>
                            Ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-cta btn-cta-primary">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Login ke Sistem
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <!-- ═══════════════════ FOOTER ═══════════════════ -->
        <footer>
            <div class="footer-inner">
                <div class="footer-brand">
                    <i class="bi bi-gear-wide-connected" style="color: var(--primary); font-size: 1rem;"></i>
                    <span>PT. Karya Manunggal Manufaktur &mdash; Sistem Preventive Maintenance TBM</span>
                </div>
                <div class="footer-copy">
                    &copy; {{ date('Y') }} PT. Karya Manunggal Manufaktur. All rights reserved.
                </div>
                <div class="footer-links">
                    <a href="{{ route('login') }}">Login</a>
                    <a href="#features">Fitur</a>
                </div>
            </div>
        </footer>

    </div><!-- /page-wrapper -->

    <!-- Particle Canvas Script -->
    <script>
        (function() {
            const canvas = document.getElementById('bg-canvas');
            const ctx = canvas.getContext('2d');
            let W = canvas.width = window.innerWidth;
            let H = canvas.height = window.innerHeight;

            const PARTICLE_COUNT = 55;
            const particles = [];

            function rand(min, max) {
                return Math.random() * (max - min) + min;
            }

            for (let i = 0; i < PARTICLE_COUNT; i++) {
                particles.push({
                    x: rand(0, W),
                    y: rand(0, H),
                    r: rand(1, 2.5),
                    vx: rand(-0.15, 0.15),
                    vy: rand(-0.15, 0.15),
                    alpha: rand(0.1, 0.45),
                    color: Math.random() > 0.5 ? '99, 138, 230' : '52, 211, 180',
                });
            }

            function draw() {
                ctx.clearRect(0, 0, W, H);

                // Draw connections
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);
                        if (dist < 130) {
                            ctx.strokeStyle = `rgba(99, 138, 230, ${0.06 * (1 - dist / 130)})`;
                            ctx.lineWidth = 0.8;
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.stroke();
                        }
                    }
                }

                // Draw particles
                particles.forEach(p => {
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(${p.color}, ${p.alpha})`;
                    ctx.fill();

                    p.x += p.vx;
                    p.y += p.vy;

                    if (p.x < -10) p.x = W + 10;
                    if (p.x > W + 10) p.x = -10;
                    if (p.y < -10) p.y = H + 10;
                    if (p.y > H + 10) p.y = -10;
                });

                requestAnimationFrame(draw);
            }

            draw();

            window.addEventListener('resize', () => {
                W = canvas.width = window.innerWidth;
                H = canvas.height = window.innerHeight;
            });
        })();
    </script>

    <!-- Register Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker berhasil didaftarkan!', reg))
                    .catch(err => console.log('Service Worker gagal didaftarkan', err));
            });
        }
    </script>
</body>

</html>
