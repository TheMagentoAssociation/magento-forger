@extends('layouts.app')

@section('content')
    @php
        $monthNames = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
    @endphp
    <div class="container">
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('leaderboard') }}" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="fas fa-arrow-left"></i> Back to All Years
                </a>
                <h2>Company Leaderboard - {{ $year }}</h2>
                <p class="text-muted">Top companies by contribution points per month</p>
            </div>
        </div>
        <div class="row g-4">
        @foreach($data as $monthNumber => $companies)
            @php
                $unclaimedCompany = null;
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0 fw-bold">{{ $monthNames[$monthNumber] }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                        @foreach($companies as $company)
                            @if ($company['name'] === 'unclaimed by company')
                                @php
                                    $unclaimedCompany = $company;
                                @endphp
                            @endif
                            @if($company['name'] !== 'unclaimed by company')
                                <li class="list-group-item d-flex justify-content-between align-items-center {{ $company['name'] === 'Adobe' ? 'bg-danger-subtle' : '' }}">
                                    <span class="fw-medium">{{ $company['name'] }}</span>
                                    <span class="badge text-bg-success rounded-pill">{{ number_format($company['points']) }}</span>
                                </li>
                            @endif
                        @endforeach
                            @if ($unclaimedCompany)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-warning-subtle text-danger">
                                <span class="fw-medium">{{ $unclaimedCompany['name'] }}</span>
                                <span class="badge text-bg-warning rounded-pill">{{ number_format($unclaimedCompany['points']) }}</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
        </div>
    </div>
@endsection
