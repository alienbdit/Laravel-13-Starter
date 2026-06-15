<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['name' => 'users.view',        'label' => 'View Users'],
            ['name' => 'users.create',      'label' => 'Create Users'],
            ['name' => 'users.edit',        'label' => 'Edit Users'],
            ['name' => 'users.delete',      'label' => 'Delete Users'],
            ['name' => 'roles.view',        'label' => 'View Roles'],
            ['name' => 'roles.create',      'label' => 'Create Roles'],
            ['name' => 'roles.edit',        'label' => 'Edit Roles'],
            ['name' => 'roles.delete',      'label' => 'Delete Roles'],
            ['name' => 'permissions.view',  'label' => 'View Permissions'],
            ['name' => 'permissions.create','label' => 'Create Permissions'],
            ['name' => 'permissions.edit',  'label' => 'Edit Permissions'],
            ['name' => 'permissions.delete','label' => 'Delete Permissions'],
            // Add your module permissions here, e.g.:
            // ['name' => 'posts.view',   'label' => 'View Posts'],
            // ['name' => 'posts.create', 'label' => 'Create Posts'],
            // ['name' => 'posts.edit',   'label' => 'Edit Posts'],
            // ['name' => 'posts.delete', 'label' => 'Delete Posts'],
        ];

        foreach ($definitions as $def) {
            Permission::firstOrCreate(['name' => $def['name']], $def);
        }

        $all = Permission::all();

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin'], ['label' => 'Super Admin']);
        $superAdmin->permissions()->sync($all->pluck('id'));

        $admin = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $admin->permissions()->sync(
            $all->whereIn('name', [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'roles.view',
                'permissions.view',
            ])->pluck('id')
        );

        $editor = Role::firstOrCreate(['name' => 'editor'], ['label' => 'Editor']);
        $editor->permissions()->sync(
            $all->whereIn('name', ['users.view'])->pluck('id')
        );

        $viewer = Role::firstOrCreate(['name' => 'viewer'], ['label' => 'Viewer']);
        $viewer->permissions()->sync(
            $all->whereIn('name', ['users.view'])->pluck('id')
        );

        $testUser = User::where('email', 'test@example.com')->first();
        $testUser?->roles()->syncWithoutDetaching([$superAdmin->id]);
    }
}
