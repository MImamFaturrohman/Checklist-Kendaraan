<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemberitahuan Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 0; color: #1e293b; }
        .wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header { background: #002a7a; padding: 20px 32px; text-align: center; }
        .header img { height: 58px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto; filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.8)); }
        .header-title { color: #fff; font-size: 18px; font-weight: 700; letter-spacing: .3px; text-transform: uppercase; }
        .header-sub { color: rgba(255,255,255,.75); font-size: 13px; margin-top: 4px; font-weight: 600; letter-spacing: .5px; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; margin-bottom: 14px; }
        .btn-wrap { text-align: center; margin: 24px 0 24px; }
        .btn { display: inline-block; background: #002a7a; color: #fff !important; text-decoration: none; padding: 13px 32px; border-radius: 8px; font-size: 15px; font-weight: 700; letter-spacing: .2px; }
        .btn:hover { background: #0038a8; }
        
        /* Highlighted warning alert block */
        .alert-box { 
            background: #fee2e2; 
            border: 1px solid #fca5a5; 
            color: #991b1b; 
            font-size: 13px; 
            font-weight: 600; 
            padding: 12px 16px; 
            border-radius: 8px; 
            margin: 20px 0; 
            line-height: 1.5;
        }
        
        .fallback { font-size: 11.5px; color: #64748b; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 0 0; margin-top: 24px; line-height: 1.6; }
        .break-all { word-break: break-all; }
        .footer { background: #f1f5f9; padding: 14px 32px; font-size: 11.5px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <img src="{{ $message->embed(public_path('images/VMS.png')) }}" alt="Vehicle Management System">
        <div class="header-title">Vehicle Management System</div>
        <div class="header-sub">Pemberitahuan Reset Password</div>
    </div>
    <div class="body">
        <p class="greeting">Halo, <strong>{{ $name }}</strong>!</p>
        <p style="font-size:14px;line-height:1.6;margin-bottom:14px;">
            Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda di Vehicle Management System (VMS).
            Silakan klik tombol di bawah ini untuk mereset password Anda:
        </p>

        <div class="btn-wrap">
            <a href="{{ $url }}" class="btn">Reset Password</a>
        </div>

        <div class="alert-box">
            Tautan reset password ini hanya berlaku selama 15 menit dan hanya dapat digunakan satu kali (1x) untuk mengubah password Anda.
        </div>

        <p style="font-size:13.5px;line-height:1.5;color:#475569;margin-bottom:0;">
            Jika Anda tidak melakukan permintaan ini, abaikan saja email ini.
        </p>

        <div class="fallback">
            Jika Anda mengalami kendala saat mengklik tombol "Reset Password", salin dan tempel URL di bawah ini ke web Anda:
            <br>
            <span class="break-all"><a href="{{ $url }}">{{ $url }}</a></span>
        </div>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Port Management Unit Suralaya<br>
        Email ini dikirim otomatis, mohon tidak membalas langsung.
    </div>
</div>
</body>
</html>
