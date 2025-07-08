@extends('layouts.app')

@section('content')
    @php
        $currentYear = date('Y');
        $currentMonth = date('m');
    @endphp
    <div class="container">
        @foreach($prs as $year)
            <div class="row">
                <div class="col-12">
                    <h3>{{ $year['year']  }} <span class="text-secondary">({{ $year['total'] }} PRs)</span> </h3>
                </div>
                @foreach($year['months'] as $month)
                    @if (!($year['year'] == $currentYear && $month['month_number'] > $currentMonth))
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 col-xl-1 mb-3">
                            <div class="card h-100 text-center calendar-card {{ $month['total'] > 0 ? 'has-link' : '' }}">
                                <div class="card-body p-2">
                                    <h6 class="card-title {{ $month['total'] === 0 ? 'text-muted' : '' }}">{{ $month['month_number'] }}</h6>
                                    <p class="card-text small {{ $month['total'] === 0 ? 'text-muted' : '' }}" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        @if ($month['total'] > 0)
                                            @php
                                                $githubUrl = "https://github.com/magento/magento2/pulls?q=is%3Apr+is%3Aopen+-label%3A%22Component%3A%22+created%3A" . $month['start'] . ".." . $month['end'];
                                            @endphp
                                            <a href="{{ $githubUrl }}" target="magentoForgerGitHub" class="text-decoration-none">
                                        @endif
                                        {{ $month['total'] }}&nbsp;PRs
                                        @if ($month['total'] > 0)
                                            </a>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
