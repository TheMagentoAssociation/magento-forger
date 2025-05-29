@extends('layouts.app')

@section('content')
    @php
        $currentYear = date('Y');
        $currentMonth = date('m');
    @endphp
    <h2 class="text-3xl font-light mb-6 text-center">Pull Requests by Month</h2>
    <div class="container">
    @foreach($prs as $year)
        <div class="row">
            <div class="col-12">
                <h3>{{ $year['year']  }} <span class="text-secondary">({{ $year['total'] }} Pull Requests)</span> </h3>
            </div>
            @foreach($year['months'] as $month)
                @if (!($year['year'] == $currentYear && $month['month_number'] > $currentMonth))
                <div class="col-1">
                    <h5 class="{{$month['total'] === 0 ? 'text-muted' : ''}}">{{ $month['month_number'] }}</h5>
                    <p class="{{$month['total'] === 0 ? 'text-muted' : ''}}">
                        @if($month['total'] > 0)
                            <a href="https://github.com/magento/magento2/pulls?q=is%3Apr%20state%3Aopen%20updated%3A{{$month['start']}}..{{$month['end']}}" target="magentoForgerGitHub">
                        @endif
                        {{ $month['total'] }}&nbsp;PRs
                        @if($month['total'] > 0)
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
