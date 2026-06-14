<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $setting = CompanySetting::first() ?? new CompanySetting();
        return view('admin.settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'currency' => 'sometimes|required|string|max:10',
            'tax_name' => 'sometimes|required|string|max:50',
            'default_tax_rate' => 'sometimes|required|numeric|min:0|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gemini_api_key' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $setting = CompanySetting::first();
        if ($setting) {
            $setting->update($validated);
        } else {
            CompanySetting::create($validated);
        }

        return redirect()->route('settings.index')->with('success', 'Company settings updated successfully.');
    }
}
