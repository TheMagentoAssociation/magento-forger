@php
    use App\Helpers\RouteLabelHelper;
    $formattedLabel = RouteLabelHelper::formatLabel(Route::currentRouteName());
@endphp
<header class="bg-white shadow py-4 mb-6">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold text-blue-600">{{ $formattedLabel }}</h1>
    </div>
</header>
