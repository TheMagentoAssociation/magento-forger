<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'GitHub Stats' }}</title>
    <meta name="description" content="GitHub PR & Issue Statistics Viewer">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite('resources/sass/app.scss', 'resources/js/app.js') {{-- Tailwind CSS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- Put in here because laravel stinks and nothing works as documented and frontend people are morons in general that just overcomplicate things for no reason -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('head')
</head>
<body class="bg-gray-100 text-gray-900 font-sans min-h-screen flex flex-col">
@include('components.universe-bar')
<nav class="navbar navbar-expand-lg navbar-primary bg-primary" data-bs-theme="dark">
    <div class="container">
        <a class="navbar-brand fs-5" href="/">
            <img src="{{ asset('assets/logo_magento_soul_white.svg') }}" alt="Logo" width="32" height="32" style="margin-top: -3px;">
            <span class="fw-light">Magento Open Source</span> Forger
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            {!! $mainMenu !!}
        </div>
        <div class="">
            @auth
                {{ Auth::user()->name }} ({{ Auth::user()->github_username }})
            @endauth

            @guest
                    <a href="{{ route('github_login') }}">Login with GitHub</a>
            @endguest

        </div>
    </div>
</nav>
@include('components.header')

<main role="main" class="flex-grow container mx-auto pt-4 px-4 py-6 transition-all duration-300 ease-in-out">
    @yield('content')
</main>

@include('components.footer')

@stack('scripts')
</body>
</html>
