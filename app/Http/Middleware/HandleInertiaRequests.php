<?php

namespace App\Http\Middleware;

use App\Models\CompanyCategory;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $companyPermissions = [];
        $user = $request->user();

        if ($user && $user->companyOwner && $user->companyOwner->company) {
            $companyPermissions = $user->companyOwner->company->permissions()->pluck('name')->toArray();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->getRoleNames(),
                    'email' => $user->email,
                    'company' => $user->companyOwner ? $user->companyOwner->company : null
                ] : null,
                'company_permission' => $companyPermissions
            ],
            'categories' => $request->routeIs('register') 
                ? CompanyCategory::select(['id', 'name'])->get() 
                : [],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}