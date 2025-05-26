<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'GitHub Stats' }}</title>
    <meta name="description" content="GitHub PR & Issue Statistics Viewer">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite('resources/css/app.css') {{-- Tailwind CSS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('head')
</head>
<body class="bg-gray-100 text-gray-900 font-sans min-h-screen flex flex-col">

@include('components.header')

<main role="main" class="flex-grow container mx-auto px-4 py-6 transition-all duration-300 ease-in-out">
    @yield('content')
</main>

@include('components.footer')

@stack('scripts')
</body>
</html>
