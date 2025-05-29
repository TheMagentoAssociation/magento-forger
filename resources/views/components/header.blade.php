@php
    use App\Helpers\RouteLabelHelper;
    $formattedLabel = RouteLabelHelper::formatLabel(Route::currentRouteName());
@endphp
<header class="bg-white shadow py-3 mb-4">
    <div class="container mx-auto px-4">
        <h1 class="display-6">{{ $formattedLabel }}</h1>
    </div>
</header>
