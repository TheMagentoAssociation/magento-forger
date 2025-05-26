@extends('layouts.app')

@section('content')
    <h2 class="text-3xl font-semibold mb-6 text-center">Monthly GitHub Stats</h2>

    <section class="mb-12">
        <h3 class="text-xl font-medium mb-3 text-blue-700">Pull Requests Over Time</h3>
        <div class="bg-white p-4 shadow rounded-xl">
            <canvas id="prChart" class="w-full h-80"></canvas>
        </div>
    </section>

    <section>
        <h3 class="text-xl font-medium mb-3 text-purple-700">Issues Over Time</h3>
        <div class="bg-white p-4 shadow rounded-xl">
            <canvas id="issueChart" class="w-full h-80"></canvas>
        </div>
    </section>
@endsection

@push('scripts')
    @include('components.charts.github-stats', ['monthlyStats' => $monthlyStats])
@endpush
