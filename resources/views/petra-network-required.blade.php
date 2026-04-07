<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koneksi Jaringan Petra Diperlukan</title>
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
            background: rgba(59,130,246,.14);
            border: 1px solid rgba(59,130,246,.30);
            color: #dbeafe;
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

        ul {
            margin: 18px 0 0;
            padding-left: 20px;
            color: #d1d5db;
            line-height: 1.9;
        }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">Petra Network Required</span>

        <h1>Koneksi Jaringan Petra Diperlukan</h1>

        <p>
            Aplikasi ini hanya dapat digunakan saat perangkat terhubung ke jaringan Petra
            atau VPN Petra.
        </p>

        <p>
            Sistem mendeteksi bahwa koneksi ke LDAP Petra tidak tersedia. Untuk alasan keamanan,
            akses ke dashboard diblokir sampai koneksi jaringan Petra aktif kembali.
        </p>

        <ul>
            <li>Aktifkan WireGuard atau VPN Petra.</li>
            <li>Pastikan koneksi ke jaringan Petra sudah berhasil.</li>
            <li>Setelah itu, lakukan login ulang ke aplikasi.</li>
        </ul>

        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Login</a>
            <a href="{{ route('petra.network.required') }}" class="btn btn-secondary">Coba Lagi</a>
        </div>
    </div>
</body>
</html>
