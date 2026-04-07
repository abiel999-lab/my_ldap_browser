<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('APP_NAME', 'LDAP Dashboard') }} - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            background: #020617;
            color: #fff;
            font-family: Arial, Helvetica, sans-serif;
        }
        .page-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .left-panel {
            background: linear-gradient(180deg, rgba(15,23,42,0.95), rgba(2,6,23,1));
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px;
        }
        .brand-row img {
            height: 52px;
        }
        .brand-title {
            font-size: 32px;
            font-weight: 700;
            line-height: 1.2;
            margin-top: 24px;
        }
        .brand-subtitle {
            color: #cbd5e1;
            font-size: 16px;
            max-width: 560px;
            margin-top: 12px;
        }
        .right-panel {
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15);
            padding: 32px;
        }
        .login-title {
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        .login-desc {
            color: #475569;
            margin-bottom: 24px;
        }
        .form-label {
            display: block;
            color: #0f172a;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .form-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 15px;
            margin-bottom: 18px;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
        }
        .btn-login {
            width: 100%;
            border: none;
            border-radius: 12px;
            background: #1e3a8a;
            color: #fff;
            font-weight: 700;
            padding: 14px 16px;
            cursor: pointer;
            font-size: 15px;
        }
        .btn-login:hover {
            background: #1d4ed8;
        }
        .error-box {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .footer-note {
            margin-top: 18px;
            color: #64748b;
            font-size: 13px;
            text-align: center;
        }
        @media (max-width: 980px) {
            .page-wrapper {
                grid-template-columns: 1fr;
            }
            .left-panel {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="left-panel">
            <div class="brand-row">
                <img src="https://my.petra.ac.id/img/logo.png" alt="PCU Logo">
            </div>

            <div class="brand-title">
                Petra LDAP Management
            </div>

            <div class="brand-subtitle">
                Local administrator access for LDAP management dashboard.
            </div>
        </div>

        <div class="right-panel">
            <div class="login-card">
                <h1 class="login-title">Sign In</h1>
                <div class="login-desc">
                    Login menggunakan akun admin lokal development.
                </div>

                @if ($errors->any())
                    <div class="error-box">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('local.login.submit') }}">
                    @csrf

                    <label class="form-label" for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="form-input"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >

                    <label class="form-label" for="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input"
                        required
                    >

                    <button type="submit" class="btn-login">
                        Sign In
                    </button>
                </form>

                <div class="footer-note">
                    {{ env('APP_NAME', 'LDAP Dashboard') }} · {{ env('APP_YEAR', date('Y')) }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
