<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <style>
        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            background: #030712;
            color: #f9fafb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 720px;
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid rgba(255,255,255,.10);
            padding: 32px;
            box-sizing: border-box;
            box-shadow: 0 20px 50px rgba(0,0,0,.35);
        }

        .badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(239,68,68,.14);
            border: 1px solid rgba(239,68,68,.30);
            color: #fecaca;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .02em;
        }

        h1 {
            margin: 18px 0 12px;
            font-size: 36px;
            line-height: 1.2;
        }

        p {
            margin: 0 0 14px;
            color: #d1d5db;
            font-size: 16px;
            line-height: 1.8;
        }

        .group-box {
            margin-top: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            color: #fff;
            font-weight: 600;
            word-break: break-word;
        }

        .error-box {
            margin-top: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(239,68,68,.12);
            border: 1px solid rgba(239,68,68,.22);
            color: #fecaca;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: .2s ease;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
            border: 1px solid rgba(37,99,235,.7);
        }

        .btn-secondary {
            background: rgba(255,255,255,.04);
            color: #fff;
            border: 1px solid rgba(255,255,255,.10);
        }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">Access Forbidden</span>

        <h1>Akses Ditolak</h1>

        <p>
            Akun berhasil login melalui Keycloak, tetapi tidak memiliki hak akses ke Petra LDAP Dashboard.
        </p>

        <p>
            Hanya akun yang tergabung pada group berikut yang diperbolehkan masuk:
        </p>

        <div class="group-box">
            {{ config('petra_auth.allowed_group') }}
        </div>

        @if (session('error'))
            <div class="error-box">
                {{ session('error') }}
            </div>
        @endif

        <div class="actions">
            <a href="{{ route('login') }}" class="btn btn-primary">Kembali ke Login</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Ke Halaman Utama</a>
        </div>
    </div>
</body>
</html>
