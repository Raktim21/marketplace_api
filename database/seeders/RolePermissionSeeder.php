<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Role::where('name', 'Super Admin')->first()) {            
            Role::create([
                'name' => 'Super Admin',
            ]);
        }

        $user = User::where('role', 1)->first();

        if ($user) {
            $user->assignRole('Super Admin');
        }

        $permissions = [
            'products',
            'groups',
            'categories',
            'coupons',
            'invoices',
            'customers',
            'user ratings',
            'blacklist',
            'payment settings',
            'general customization',
            'banner customization',
            'notification settings',
            'theme settings',
            'developer'
        ];

        foreach ($permissions as $key => $permission) {
            $if_exists = Permission::where('name', $permission)->first();

            if (!$if_exists) {
                Permission::create([
                    'name' => $permission,
                ]);
            }

            $role = Role::where('name', 'Super Admin')->first();
            $role->givePermissionTo($permission);
        }
    }
}
