<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('id', 'asc')->paginate(10);
        return Inertia::render('permissions/index', ['permissions' => $permissions]);
    }

    public function store(Request $request)
    {
        $datas = $request->validate([
            'name' => 'required|string|unique:permissions,name|max:255',
            'type' => 'required|string|in:standard,unique',
        ]);

        $permission = Permission::create([...$datas, 'guard_name' => 'web']);

        if ($request->type === 'standard') {
            $owner = Role::where('name', 'company-owner')->where('guard_name', 'web')->first();
            if ($owner) {
                $owner->givePermissionTo($permission);
            }
        }

        return redirect()->back()->with('success', 'Permission created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $datas = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $id . '|max:255',
            'type' => 'required|string|in:standard,unique',
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update($datas);

        return redirect()->back()->with('success', 'Permission updated successfully.');
    }

    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->back()->with('success', 'Permission deleted successfully.');
    }
}