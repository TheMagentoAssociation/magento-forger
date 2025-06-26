@extends('layouts.app')

@section('content')
    <div class="row">
        @php $columnCount = 0; @endphp

        @foreach($labels as $prefix => $labelGroup)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        {{ $prefix === 'no_prefix' ? 'Other Labels' : $prefix }}
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach($labelGroup as $labelData)
                            @php
                                // Wrap the label in quotes, then URL-encode it
                                $quotedLabel = urlencode('"' . $labelData['label'] . '"');
                                $githubUrl = "https://github.com/magento/magento2/issues?q=is%3Aissue+is%3Aopen+label%3A{$quotedLabel}";
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ $githubUrl }}" target="magentoForgerGitHub" class="text-decoration-none">
                                    {{ $labelData['label'] }}
                                </a>
                                <span class="badge bg-primary rounded-pill">{{ $labelData['count'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            @php
                $columnCount++;
                if ($columnCount % 2 === 0) {
                    echo '</div><div class="row">';
                }
            @endphp
        @endforeach
    </div>
@endsection
