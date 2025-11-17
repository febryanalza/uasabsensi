<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Sistem Informasi Absensi Perusahaan - Kelola Kehadiran Karyawan')</title>
    <meta name="description" content="Sistem Informasi Absensi Perusahaan adalah platform digital terpercaya untuk mengelola kehadiran karyawan dengan teknologi RFID modern.">
    
    <!-- Local Fonts & Icons (No CDN dependencies) -->
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; }
        .fas, .fab, .far { font-family: monospace; }
        .fas::before { content: "["; }
        .fas::after { content: "]"; }
    </style>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    @include('components.header')

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer')

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
