<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $stats = [];
        $activities = [];

        if ($user->hasRole('super-admin')) {
            $stats = [
                ['title' => 'Total Companies', 'value' => Company::count(), 'icon'  => 'Building2', 'desc'  => 'Registered companies'],
                ['title' => 'Total Permissions', 'value' => Permission::count(), 'icon'  => 'ShieldCheck', 'desc'  => 'Available features'],
                ['title' => 'System Alerts', 'value' => 0,  'icon'  => 'ShieldAlert', 'desc'  => 'Critical issues']
            ];

            $activities = Company::latest()->take(5)->get()->map(function($company) {
                return [
                    'title' => 'New Registration',
                    'desc'  => "{$company->name} joined the platform.",
                    'time'  => $company->created_at->diffForHumans(),
                ];
            });

        } else {
            $company = $user->companyOwner?->company;

            if ($company) {
                $company->load('permissions');
                
                $stats = [
                    ['title' => 'Active Features', 'value' => $company->permissions->count(), 'icon'  => 'Zap', 'desc'  => 'Features enabled for you'],
                    ['title' => 'Company Members', 'value' => 1, 'icon'  => 'Users', 'desc'  => 'Staff & collaborators'],
                    ['title' => 'Workspaces', 'value' => 0, 'icon'  => 'Briefcase', 'desc'  => 'Active workspaces']
                ];
            }
        }

        return Inertia::render('dashboard', [
            'stats'      => $stats,
            'activities' => $activities
        ]);
    }
}