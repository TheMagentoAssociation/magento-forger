@extends('layouts.app')

@section('content')
    <div class="container max-w-2xl py-8 mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit Employment</h1>

        <form method="POST" action="{{ route('employment.update', $affiliation->id) }}">
            @csrf
            @method('PUT')
            @if($errors->has('conflict'))
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first('conflict') }}
                </div>
            @endif
            <div class="mb-4">
                <label class="block font-semibold mb-1">Company</label>
                <select name="company_id" class="w-full border px-3 py-2 rounded">
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ $affiliation->company_id == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $affiliation->start_date }}" class="w-full border px-3 py-2 rounded">
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1">End Date (optional)</label>
                <input type="date" name="end_date" value="{{ $affiliation->end_date }}" class="w-full border px-3 py-2 rounded">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </form>
    </div>
@endsection
