<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Coming Soon' }} - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="dash-body">
        <div class="dash-shell flex items-center justify-center p-6">
            <div class="bg-white w-full max-w-xl rounded-3xl shadow-xl p-8 text-center">
                <p class="dash-section-title mb-3">SEGERA HADIR</p>
                <h1 class="text-3xl font-extrabold text-slate-900">{{ $title ?? 'Halaman Baru' }}</h1>
                <p class="text-slate-600 mt-3">{{ $message ?? 'Fitur sedang dalam proses pengembangan.' }}</p>

                <a href="{{ route('dashboard') }}" class="inline-flex mt-6 rounded-full bg-blue-900 text-white px-5 py-2.5 font-semibold">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </body>
</html>
