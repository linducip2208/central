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
            ['section' => 'Perencanaan'],
            ['label' => 'Demand', 'icon' => 'ti ti-chart-bar', 'children' => [
                ['label' => 'Demand Harian', 'route' => 'demands.index', 'perm' => 'demand.view'],
                ['label' => 'Demand Plans', 'route' => 'demand-plans.index', 'perm' => 'demand.view'],
                ['label' => 'MRP', 'route' => 'mrp.index', 'perm' => 'mrp.view'],
                ['label' => 'BOM', 'route' => 'boms.index', 'perm' => 'bom.view'],
            ]],
            ['section' => 'Operasional'],
            ['label' => 'Procurement', 'icon' => 'ti ti-shopping-cart', 'children' => [
                ['label' => 'Purchase Request', 'route' => 'purchase-requests.index', 'perm' => 'pr.view'],
                ['label' => 'RFQ & Quotation', 'route' => 'rfqs.index', 'perm' => 'rfq.view'],
                ['label' => 'Purchase Order', 'route' => 'purchase-orders.index', 'perm' => 'po.view'],
                ['label' => 'Goods Receipt', 'route' => 'goods-receipts.index', 'perm' => 'gr.view'],
                ['label' => 'Supplier Invoice', 'route' => 'invoices.index', 'perm' => 'invoice.view'],
            ]],
            ['label' => 'Inventory', 'icon' => 'ti ti-box', 'children' => [
                ['label' => 'Stock', 'route' => 'inventory.index', 'perm' => 'inventory.view'],
                ['label' => 'Movements', 'route' => 'inventory.movements', 'perm' => 'inventory.view'],
                ['label' => 'Batches', 'route' => 'batches.index', 'perm' => 'inventory.view'],
                ['label' => 'Lokasi WMS', 'route' => 'wms.locations', 'perm' => 'wms.view'],
                ['label' => 'Scan Barcode', 'route' => 'wms.scan', 'perm' => 'wms.view'],
                ['label' => 'Traceability', 'route' => 'trace.form', 'perm' => 'inventory.view'],
                ['label' => 'Stock Opname', 'route' => 'stock-opnames.index', 'perm' => 'opname.view'],
            ]],
            ['label' => 'Production', 'icon' => 'ti ti-chef-hat', 'children' => [
                ['label' => 'Planning', 'route' => 'production-plans.index', 'perm' => 'production.view'],
                ['label' => 'Orders', 'route' => 'production-orders.index', 'perm' => 'production.view'],
                ['label' => 'Capacity', 'route' => 'capacity.index', 'perm' => 'production.view'],
                ['label' => 'QC Klasik', 'route' => 'quality-controls.index', 'perm' => 'qc.view'],
                ['label' => 'Inspeksi QMS', 'route' => 'inspections.index', 'perm' => 'qms.view'],
                ['label' => 'NCR / CAPA', 'route' => 'ncrs.index', 'perm' => 'qms.view'],
                ['label' => 'Suhu CCP', 'route' => 'temp.index', 'perm' => 'qms.view'],
                ['label' => 'Packaging', 'route' => 'packagings.index', 'perm' => 'production.view'],
                ['label' => 'Recall', 'route' => 'recalls.index', 'perm' => 'recall.view'],
            ]],
            ['label' => 'Distribution', 'icon' => 'ti ti-truck', 'children' => [
                ['label' => 'Distributions', 'route' => 'distributions.index', 'perm' => 'distribution.view'],
                ['label' => 'Deliveries', 'route' => 'deliveries.index', 'perm' => 'delivery.view'],
                ['label' => 'Rute', 'route' => 'tms.routes', 'perm' => 'tms.view'],
                ['label' => 'Control Tower', 'route' => 'tms.tower', 'perm' => 'tms.view'],
                ['label' => 'Portal Sekolah', 'route' => 'portal.index', 'perm' => 'portal.view'],
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
            ['label' => 'Katalog', 'icon' => 'ti ti-archive', 'children' => [
                ['label' => 'Alergen', 'route' => 'catalog.allergens', 'perm' => 'catalog.view'],
                ['label' => 'Kelompok Diet', 'route' => 'catalog.meal-groups', 'perm' => 'catalog.view'],
                ['label' => 'Kendaraan', 'route' => 'catalog.vehicles', 'perm' => 'catalog.view'],
                ['label' => 'Work Center', 'route' => 'catalog.work-centers', 'perm' => 'catalog.view'],
            ]],
            ['section' => 'Lainnya'],
            ['label' => 'Waste', 'route' => 'wastes.index', 'icon' => 'ti ti-trash', 'perm' => 'waste.view'],
            ['label' => 'Costing', 'route' => 'costings.index', 'icon' => 'ti ti-coins', 'perm' => 'costing.view'],
            ['label' => 'Analytics', 'route' => 'analytics.executive', 'icon' => 'ti ti-dashboard', 'perm' => 'report.view'],
            ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'ti ti-report', 'perm' => 'report.view'],
            ['label' => 'Notifications', 'route' => 'notifications.index', 'icon' => 'ti ti-bell', 'perm' => null],
            ['section' => 'Sistem'],
            ['label' => 'Approvals', 'route' => 'approvals.inbox', 'icon' => 'ti ti-checklist', 'perm' => 'approval.view'],
            ['label' => 'Webhooks', 'route' => 'webhooks.index', 'icon' => 'ti ti-webhook', 'perm' => 'webhook.view'],
            ['label' => 'Users', 'route' => 'users.index', 'icon' => 'ti ti-users', 'perm' => 'user.view'],
            ['label' => 'Roles', 'route' => 'roles.index', 'icon' => 'ti ti-key', 'perm' => 'role.view'],
            ['label' => 'Audit Log', 'route' => 'audit-logs.index', 'icon' => 'ti ti-history', 'perm' => 'audit.view'],
            ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'ti ti-settings', 'perm' => 'setting.view'],
        ];
    }
}
