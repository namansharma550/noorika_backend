<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    protected array $resources = [
        'category', 'product', 'collection', 'order', 'coupon',
        'review', 'page', 'newsletter_subscriber', 'user', 'address',
        'payment', 'wishlist', 'setting', 'contact_enquiry',
    ];

    protected array $actions = ['view', 'create', 'update', 'delete'];

    public function run(): void
    {
        $permissions = [];
        foreach ($this->resources as $resource) {
            foreach ($this->actions as $action) {
                $permissions[] = "{$action}_{$resource}";
            }
        }

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        // Manager: full catalog + order + marketing control, no user/settings management.
        $managerResources = ['category', 'product', 'collection', 'order', 'coupon', 'review', 'page', 'newsletter_subscriber', 'contact_enquiry'];
        $managerPermissions = collect($managerResources)
            ->flatMap(fn ($r) => collect($this->actions)->map(fn ($a) => "{$a}_{$r}"))
            ->all();
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions($managerPermissions);

        // Support: read-only across the board, plus order status updates.
        $supportPermissions = collect($this->resources)
            ->map(fn ($r) => "view_{$r}")
            ->push('update_order')
            ->all();
        $support = Role::firstOrCreate(['name' => 'Support', 'guard_name' => 'web']);
        $support->syncPermissions($supportPermissions);

        $admin = User::where('email', 'admin@noorika.test')->first();
        $admin?->syncRoles(['Super Admin']);
    }
}
