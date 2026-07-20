<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PM Scheduling System</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/png" href="{{ asset('zani.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('zani.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('zani.png') }}">

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1E3A8A">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, .15) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            animation: float 8s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, .1) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            animation: float 6s ease-in-out infinite reverse;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(30px, -30px);
            }
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: rgba(255, 255, 255, .05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, .25);
        }

        .login-logo {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 1.75rem;
            color: #fff;
            box-shadow: 0 8px 20px rgba(59, 130, 246, .3);
        }

        .login-title {
            color: #fff;
            font-weight: 800;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: .25rem;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, .5);
            font-size: .85rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-floating label {
            color: rgba(255, 255, 255, .5);
        }

        .form-floating .form-control {
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .12);
            color: #fff;
            border-radius: 10px;
            height: 52px;
        }

        .form-floating .form-control:focus {
            background: rgba(255, 255, 255, .1);
            border-color: #3b82f6;
            box-shadow: 0 0 0 .2rem rgba(59, 130, 246, .2);
            color: #fff;
        }

        .form-floating .form-control::placeholder {
            color: transparent;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: rgba(255, 255, 255, .7);
            cursor: pointer;
            padding: .25rem;
        }

        .password-toggle:hover {
            color: #fff;
        }

        .btn-login {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            border-radius: 10px;
            height: 48px;
            font-size: .95rem;
            transition: all .3s ease;
            width: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, .4);
            color: #fff;
        }

        .form-check-label {
            color: rgba(255, 255, 255, .6);
            font-size: .85rem;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, .1);
            border-color: rgba(255, 255, 255, .2);
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .demo-accounts {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        .demo-accounts h6 {
            color: rgba(255, 255, 255, .4);
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            margin-bottom: .75rem;
        }

        .demo-account-item {
            background: rgba(255, 255, 255, .05);
            border-radius: 8px;
            padding: .5rem .75rem;
            margin-bottom: .4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .demo-account-item .role-badge {
            font-size: .65rem;
            font-weight: 700;
            padding: .2em .6em;
            border-radius: 5px;
        }

        .demo-account-item .email-text {
            color: rgba(255, 255, 255, .7);
            font-size: .8rem;
            font-family: monospace;
        }

        .alert {
            border-radius: 10px;
            font-size: .85rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
            <h4 class="login-title">PM Scheduling</h4>
            <p class="login-subtitle">Sistem Penjadwalan Preventive Maintenance</p>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    @foreach ($errors->all() as $error)
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $error }}<br>
                    @endforeach
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST">
                @csrf
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                        value="{{ old('email') }}" required autofocus>
                    <label for="email"><i class="bi bi-envelope me-1"></i> Email</label>
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                        required>
                    <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
                    <button type="button" class="password-toggle" data-target="password"
                        aria-label="Tampilkan password">
                        <i class="bi bi-eye" id="passwordToggleIcon"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('home') }}" class="text-white-50 small text-decoration-none">
                    <i class="bi bi-arrow-left-circle me-1"></i> Kembali ke Beranda
                </a>
            </div>

            <!-- <div class="demo-accounts">
                <h6>Demo Accounts (password: password)</h6>
                <div class="demo-account-item">
                    <span class="email-text">admin@pm-system.com</span>
                    <span class="role-badge bg-primary">Admin</span>
                </div>
                <div class="demo-account-item">
                    <span class="email-text">teknisi1@pm-system.com</span>
                    <span class="role-badge bg-success">Teknisi</span>
                </div>
            </div> -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            const toggleIcon = document.getElementById('passwordToggleIcon');

            if (!passwordInput || !toggleButton || !toggleIcon) {
                return;
            }

            toggleButton.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleIcon.classList.toggle('bi-eye');
                toggleIcon.classList.toggle('bi-eye-slash');
                toggleButton.setAttribute('aria-label', isPassword ? 'Sembunyikan password' :
                    'Tampilkan password');
            });
        });
    </script>
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
