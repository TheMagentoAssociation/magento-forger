<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyRecommendationController extends Controller
{
    public function create()
    {
        return view('companies.recommend');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
        ]);

        // Either create a new company or update if it already exists
        $company = Company::firstOrCreate(
            ['name' => $request->company_name],
            ['is_magento_member' => false]
        );

        // Mark as recommended
        $company->is_recommended = true;
        $company->save();

        return back()->with('status', 'Company recommended successfully.');
    }
}
