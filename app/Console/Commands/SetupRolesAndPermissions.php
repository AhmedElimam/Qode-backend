<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupRolesAndPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-roles-and-permissions';

    protected $description = 'Setup roles and permissions for the news aggregation system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up roles and permissions...');

        $this->createRoles();
        $this->createPermissions();

        $this->info('Roles and permissions setup completed!');
    }

    private function createRoles(): void
    {
        $roles = ['admin', 'user', 'moderator'];

        foreach ($roles as $role) {
            if (!\Spatie\Permission\Models\Role::where('name', $role)->exists()) {
                \Spatie\Permission\Models\Role::create(['name' => $role]);
                $this->info("Created role: {$role}");
            } else {
                $this->info("Role already exists: {$role}");
            }
        }
    }

    private function createPermissions(): void
    {
        $permissions = [
            'view articles',
            'search articles',
            'manage preferences',
            'refresh articles',
            'manage users',
            'manage system',
        ];

        foreach ($permissions as $permission) {
            if (!\Spatie\Permission\Models\Permission::where('name', $permission)->exists()) {
                \Spatie\Permission\Models\Permission::create(['name' => $permission]);
                $this->info("Created permission: {$permission}");
            } else {
                $this->info("Permission already exists: {$permission}");
            }
        }

        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        $userRole = \Spatie\Permission\Models\Role::where('name', 'user')->first();
        $moderatorRole = \Spatie\Permission\Models\Role::where('name', 'moderator')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo(\Spatie\Permission\Models\Permission::all());
            $this->info('Assigned all permissions to admin role');
        }

        if ($userRole) {
            $userRole->givePermissionTo([
                'view articles',
                'search articles',
                'manage preferences',
            ]);
            $this->info('Assigned user permissions to user role');
        }

        if ($moderatorRole) {
            $moderatorRole->givePermissionTo([
                'view articles',
                'search articles',
                'manage preferences',
                'refresh articles',
                'manage users',
            ]);
            $this->info('Assigned moderator permissions to moderator role');
        }
    }
}
