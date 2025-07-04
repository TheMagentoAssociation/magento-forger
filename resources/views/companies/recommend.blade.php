@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Recommend a Company</h1>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('companies.recommend') }}">
            @csrf
            <div class="form-group">
                <label for="company_name">Company Name</label>
                <input type="text" name="company_name" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Recommend Company</button>
        </form>
    </div>
@endsection
