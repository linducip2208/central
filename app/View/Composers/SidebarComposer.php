<?php

namespace App\View\Composers;

use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $menu = config('mbg_menu', []);
        if (empty($menu)) {
            $menu = $this->defaultMenu();
        }
        $view->with('mbgMenu', $menu);
    }

    protected function defaultMenu(): array
    {
        return [
            ['section' => 'Utama'],
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'ti ti-dashboard', 'perm' => null],
            ['section' => 'Operasional'],
            ['label' => 'Demand Planning', 'route' => 'demands.index', 'icon' => 'ti ti-chart-bar', 'perm' => 'demand.view'],
            ['label' => 'Procurement', 'icon' => 'ti ti-shopping-cart', 'children' => [
                ['label' => 'Purchase Request', 'route' => 'purchase-requests.index', 'perm' => 'pr.view'],
                ['label' => 'Purchase Order', 'route' => 'purchase-orders.index', 'perm' => 'po.view'],
                ['label' => 'Goods Receipt', 'route' => 'goods-receipts.index', 'perm' => 'gr.view'],
            ]],
            ['label' => 'Inventory', 'icon' => 'ti ti-box', 'children' => [
                ['label' => 'Stock', 'route' => 'inventory.index', 'perm' => 'inventory.view'],
                ['label' => 'Movements', 'route' => 'inventory.movements', 'perm' => 'inventory.view'],
                ['label' => 'Batches', 'route' => 'batches.index', 'perm' => 'inventory.view'],
                ['label' => 'Stock Opname', 'route' => 'stock-opnames.index', 'perm' => 'opname.view'],
            ]],
            ['label' => 'Production', 'icon' => 'ti ti-chef-hat', 'children' => [
                ['label' => 'Planning', 'route' => 'production-plans.index', 'perm' => 'production.view'],
                ['label' => 'Orders', 'route' => 'production-orders.index', 'perm' => 'production.view'],
                ['label' => 'QC', 'route' => 'quality-controls.index', 'perm' => 'qc.view'],
                ['label' => 'Packaging', 'route' => 'packagings.index', 'perm' => 'production.view'],
            ]],
            ['label' => 'Distribution', 'icon' => 'ti ti-truck', 'children' => [
                ['label' => 'Distributions', 'route' => 'distributions.index', 'perm' => 'distribution.view'],
                ['label' => 'Deliveries', 'route' => 'deliveries.index', 'perm' => 'delivery.view'],
            ]],
            ['section' => 'Master Data'],
            ['label' => 'Products', 'route' => 'products.index', 'icon' => 'ti ti-package', 'perm' => 'product.view'],
            ['label' => 'Ingredients', 'route' => 'ingredients.index', 'icon' => 'ti ti-carrot', 'perm' => 'product.view'],
            ['label' => 'Units', 'route' => 'units.index', 'icon' => 'ti ti-ruler', 'perm' => 'product.view'],
            ['label' => 'Menus & Recipes', 'icon' => 'ti ti-book', 'children' => [
                ['label' => 'Menus', 'route' => 'menus.index', 'perm' => 'menu.view'],
                ['label' => 'Recipes', 'route' => 'recipes.index', 'perm' => 'menu.view'],
            ]],
            ['label' => 'Partners', 'icon' => 'ti ti-building', 'children' => [
                ['label' => 'Suppliers', 'route' => 'suppliers.index', 'perm' => 'supplier.view'],
                ['label' => 'Schools', 'route' => 'schools.index', 'perm' => 'school.view'],
                ['label' => 'Recipients', 'route' => 'recipients.index', 'perm' => 'school.view'],
            ]],
            ['label' => 'Facilities', 'icon' => 'ti ti-home', 'children' => [
                ['label' => 'Central Kitchens', 'route' => 'central-kitchens.index', 'perm' => 'org.view'],
                ['label' => 'Kitchen Units', 'route' => 'kitchen-units.index', 'perm' => 'org.view'],
                ['label' => 'Warehouses', 'route' => 'warehouses.index', 'perm' => 'org.view'],
            ]],
            ['section' => 'Lainnya'],
            ['label' => 'Waste', 'route' => 'wastes.index', 'icon' => 'ti ti-trash', 'perm' => 'waste.view'],
            ['label' => 'Costing', 'route' => 'costings.index', 'icon' => 'ti ti-coins', 'perm' => 'costing.view'],
            ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'ti ti-report', 'perm' => 'report.view'],
            ['label' => 'Notifications', 'route' => 'notifications.index', 'icon' => 'ti ti-bell', 'perm' => null],
            ['section' => 'Sistem'],
            ['label' => 'Users', 'route' => 'users.index', 'icon' => 'ti ti-users', 'perm' => 'user.view'],
            ['label' => 'Roles', 'route' => 'roles.index', 'icon' => 'ti ti-key', 'perm' => 'role.view'],
            ['label' => 'Audit Log', 'route' => 'audit-logs.index', 'icon' => 'ti ti-history', 'perm' => 'audit.view'],
            ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'ti ti-settings', 'perm' => 'setting.view'],
        ];
    }
}
