<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionAccessController extends Controller
{
    public function index()
    {
        $companies = Company::with('permissions')->orderBy('name', 'asc')->paginate(10);
        
        return Inertia::render('permissions/access', [
            'companies'           => $companies,
            'uniquePermissions'   => Permission::where('type', 'unique')->get(),
            'standardPermissions' => Permission::where('type', 'standard')->get(),
        ]);
    }
    
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'permission_name' => 'required|exists:permissions,name',
            'enabled'         => 'required|boolean',
        ]);

        $permission = Permission::where('name', $request->permission_name)->firstOrFail();

        if ($request->enabled) {
            $company->givePermissionTo($permission->name);
            $message = "Access '{$permission->name}' granted to {$company->name}.";
        } else {
            $company->revokePermissionTo($permission->name);
            $message = "Access '{$permission->name}' revoked from {$company->name}.";
        }

        return redirect()->back()->with('success', $message);
    }
}