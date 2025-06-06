@extends('layouts.app')

@section('content')
    @php
        $currentYear = date('Y');
        $currentMonth = date('m');
    @endphp
    <div class="container">
        <div class="row row-cols-3 g-4">
        @foreach($data as $year => $companies)
            @php
                $unclaimedCompany = null;
            @endphp
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $year  }} </h5>
                        <ul class="list-group">

                        @foreach($companies as $company)

                            @if ($company['name'] === 'unclaimed by company')
                                @php
                                    $unclaimedCompany = $company;
                                @endphp
                            @endif
                            @if($company['name'] !== 'unclaimed by company')
                                <li class="list-group-item d-flex justify-content-between align-items-center {{ $company['name'] === 'Adobe' ? 'bg-danger-subtle' : '' }}">
                                    {{ $company['name'] }}
                                    <span class="badge text-bg-success rounded-pill">{{ number_format($company['points']) }}</span>
                                </li>
                                @endif
                        @endforeach
                            @if ($unclaimedCompany)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-warning-subtle text-danger">
                                {{ $unclaimedCompany['name'] }}
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
