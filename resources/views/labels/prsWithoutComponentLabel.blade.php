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
                        <div class="col-1">
                            <h5 class="{{ $month['total'] === 0 ? 'text-muted' : '' }}">{{ $month['month_number'] }}</h5>
                            <p class="{{ $month['total'] === 0 ? 'text-muted' : '' }}">
                                @if ($month['total'] > 0)
                                    @php
                                        $githubUrl = "https://github.com/magento/magento2/pulls?q=is%3Apr+is%3Aopen+-label%3A%22Component%3A%22+created%3A" . $month['start'] . ".." . $month['end'];
                                    @endphp
                                    <a href="{{ $githubUrl }}" target="magentoForgerGitHub">
                                        @endif
                                        {{ $month['total'] }}&nbsp;PRs
                                        @if ($month['total'] > 0)
                                    </a>
                                @endif
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
