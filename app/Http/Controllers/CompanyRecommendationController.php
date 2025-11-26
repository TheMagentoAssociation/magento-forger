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

        // Sanitize company name to prevent XSS
        $companyName = strip_tags(trim($request->company_name));

        // Either create a new company or update if it already exists
        $company = Company::firstOrCreate(
            ['name' => $companyName]
        );

        // Mark as recommended (using direct assignment to bypass guarded protection)
        // This is intentional - users can recommend companies, admins set magento_member status
        $company->is_recommended = true;
        $company->save();

        return back()->with('status', 'Company recommended successfully.');
    }
}
