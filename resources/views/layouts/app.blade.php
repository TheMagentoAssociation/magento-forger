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
    @stack('head')
</head>
<body class="bg-gray-100 text-gray-900 font-sans min-h-screen flex flex-col">
<nav class="navbar navbar-expand-lg navbar-primary bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">Magento Forger</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link {{ Route::is('home') ? 'active' : ''}}" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ Route::is('issuesByMonth') ? 'active' : ''}}" href="{{route('issuesByMonth')}}">Issues By Month</a></li>
            </ul>
        </div>
    </div>
</nav>
@include('components.header')

<main role="main" class="flex-grow container mx-auto px-4 py-6 transition-all duration-300 ease-in-out">
    @yield('content')
</main>

@include('components.footer')

@stack('scripts')
</body>
</html>
