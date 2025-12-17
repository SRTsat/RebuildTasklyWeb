<?php

namespace Database\Seeders;

use App\Models\CompanyCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdminRole = Role::create(['name' => 'super-admin']);
        $ownerCompanyRole = Role::create(['name' => 'company-owner']);

        $categories = [
            'Technology & Software',
            'Marketing & Creative',
            'Construction & Real Estate',
            'Education & Training',
            'Finance & Accounting',
            'Healthcare',
            'Other'
        ];

        foreach ($categories as $category) {
            CompanyCategory::create([
                'name' => $category,
                'slug' => Str::slug($category),
            ]);
        }

        $superAdmin = User::create([
            'name'              => 'Super Admin',
            'email'             => 'superadmin@localhost.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
            'two_factor_secret'         => null,
        ]);
        $superAdmin->assignRole($superAdminRole);
    }
}