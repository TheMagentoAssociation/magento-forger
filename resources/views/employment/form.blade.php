@extends('layouts.app')

@section('content')
    @if($affiliations->count())
        <div class="container">
            <h3 class="">Your Employment History</h3>
            <table class="w-full table-auto border">
                <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 text-left">Company</th>
                    <th class="px-4 py-2">Start</th>
                    <th class="px-4 py-2">End</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($affiliations as $a)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $a->company->name }}</td>
                        <td class="px-4 py-2">{{ $a->start_date }}</td>
                        <td class="px-4 py-2">{{ $a->end_date ?? 'Present' }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('employment.edit', $a->id) }}" class="btn btn-info">Edit</a>

                            <form method="POST" action="{{ route('employment.destroy', $a->id) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <hr>
    @endif
    <div class="container mb-4">
        <h3 class="">Add Employment History</h3>

        @if(session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->has('conflict'))
            <div class="alert alert-danger" role="alert">
                {{ $errors->first('conflict') }}
            </div>
        @endif

        <form method="POST" action="{{ url('/employment') }}">
            @csrf

            <div class="row mb-3">
                <label for="company_id" class="col-sm-2 col-form-label">Select Company</label>
                <div class="col-sm-10">
                    <select name="company_id" id="company_id" class="form-control">
                        <option value="">-- Select a Company --</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Select the company you work (or worked) for.
                    </div>
                </div>
                @error('company_id')
                <div class="alert alert-danger" role="alert">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mb-3">
                <label for="start_date" class="col-sm-2 col-form-label">Start Date</label>
                <div class="col-sm-10">
                    <input type="date" name="start_date" id="start_date" class="w-full border border-gray-300 rounded px-3 py-2" required>
                    @error('start_date')
                    <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Select the date when you started working for the company above.
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <label for="end_date" class="col-sm-2 col-form-label">End Date (optional)</label>
                <div class="col-sm-10">
                    <input type="date" name="end_date" id="end_date" class="w-full border border-gray-300 rounded px-3 py-2">
                    @error('end_date')
                    <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Select the date when you stopped working for the company above. If you're currently still employed at the company, just leave this empty.
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success">
                Save Employment History
            </button>
        </form>
    </div>
@endsection
