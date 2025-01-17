<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $role = [
            [
                'name' => 'super_admin',
                'created_at' => now(),
            ],
            [
                'name' => 'manager',
                'created_at' => now(),
            ],
            [
                'name' => 'employee',
                'created_at' => now(),
            ],
        ];
        $createRole = Role::insert($role);

        // Create permissions
        $permission = [
            [
                'name' => 'create.company',
                'created_at' => now()
            ],
            [
                'name' => 'read.company',
                'created_at' => now()
            ],
            [
                'name' => 'update.company',
                'created_at' => now()
            ],
            [
                'name' => 'delete.company',
                'created_at' => now()
            ],
            [
                'name' => 'create.manager',
                'created_at' => now()
            ],
            [
                'name' => 'read.manager',
                'created_at' => now()
            ],
            [
                'name' => 'update.manager',
                'created_at' => now()
            ],
            [
                'name' => 'delete.manager',
                'created_at' => now()
            ],
            [
                'name' => 'create.employee',
                'created_at' => now()
            ],
            [
                'name' => 'read.employee',
                'created_at' => now()
            ],
            [
                'name' => 'update.employee',
                'created_at' => now()
            ],
            [
                'name' => 'delete.employee',
                'created_at' => now()
            ],
        ];
        $createPermission = Permission::insert($permission);

        // Attach permissions to super admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdminRole->permissions()->attach([1, 2, 3, 4, 8]);

        // Attach permissions to manager role
        $managerRole = Role::where('name', 'manager')->first();
        $managerRole->permissions()->attach([6, 7, 9, 10, 11, 12]);

        // Attach permissions to employee role
        $employeeRole = Role::where('name', 'employee')->first();
        $employeeRole->permissions()->attach([10]);

        // Create users
        $user = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 1,
                'created_at' => now()
            ],
            [
                'name' => 'Employee',
                'email' => 'employee@gmail.com',
                'password' => Hash::make('password'),
                'role_id' => 3,
                'created_at' => now()
            ]
        ];

        $createSuperAdminUser = User::insert($user);
    }
}
