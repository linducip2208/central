<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\CapacityController;
use App\Http\Controllers\CostingController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DemandController;
use App\Http\Controllers\DemandPlanController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MasterCatalogController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MrpController;
use App\Http\Controllers\PackagingController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ProductionPlanController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\QualityControlController;
use App\Http\Controllers\QualityInspectionController;
use App\Http\Controllers\RecallController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierInvoiceController;
use App\Http\Controllers\TmsController;
use App\Http\Controllers\TraceController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WasteController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WmsController;
use Illuminate\Support\Facades\Route;

// Master: organisasi & fasilitas
Route::middleware('permission:org.view')->group(function () {
    Route::get('/organizations', [FacilityController::class, 'orgIndex'])->name('organizations.index');
    Route::post('/organizations', [FacilityController::class, 'orgStore'])->name('organizations.store')->middleware('permission:org.create');
    Route::get('/central-kitchens', [FacilityController::class, 'kitchenIndex'])->name('central-kitchens.index');
    Route::post('/central-kitchens', [FacilityController::class, 'kitchenStore'])->name('central-kitchens.store')->middleware('permission:org.create');
    Route::get('/central-kitchens/{kitchen}', [FacilityController::class, 'kitchenShow'])->name('central-kitchens.show');
    Route::get('/kitchen-units', [FacilityController::class, 'unitIndex'])->name('kitchen-units.index');
    Route::post('/kitchen-units', [FacilityController::class, 'unitStore'])->name('kitchen-units.store')->middleware('permission:org.create');
    Route::get('/warehouses', [FacilityController::class, 'warehouseIndex'])->name('warehouses.index');
    Route::post('/warehouses', [FacilityController::class, 'warehouseStore'])->name('warehouses.store')->middleware('permission:org.create');
});

// Partners
Route::middleware('permission:supplier.view')->group(function () {
    Route::resource('suppliers', SupplierController::class);
    Route::post('/suppliers/{supplier}/contacts', [SupplierController::class, 'storeContact'])->name('suppliers.contacts.store');
    Route::post('/suppliers/{supplier}/addresses', [SupplierController::class, 'storeAddress'])->name('suppliers.addresses.store');
    Route::post('/suppliers/{supplier}/contracts', [SupplierController::class, 'storeContract'])->name('suppliers.contracts.store');
    Route::post('/suppliers/{supplier}/prices', [SupplierController::class, 'storePrice'])->name('suppliers.prices.store');
});
Route::middleware('permission:school.view')->group(function () {
    Route::resource('schools', SchoolController::class);
    Route::get('/recipients', [RecipientController::class, 'index'])->name('recipients.index');
    Route::post('/recipients', [RecipientController::class, 'store'])->name('recipients.store');
    Route::put('/recipients/{recipient}', [RecipientController::class, 'update'])->name('recipients.update');
    Route::delete('/recipients/{recipient}', [RecipientController::class, 'destroy'])->name('recipients.destroy');
    Route::post('/schools/{school}/recipients', [SchoolController::class, 'storeRecipient'])->name('schools.recipients.store');
    Route::delete('/recipients/{recipient}/from-school', [SchoolController::class, 'destroyRecipient'])->name('recipients.destroy.from-school');
});

// Produk & bahan
Route::middleware('permission:product.view')->group(function () {
    Route::resource('ingredients', IngredientController::class);
    Route::resource('products', ProductController::class);
    Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    Route::post('/units', [UnitController::class, 'store'])->name('units.store');
    Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
    Route::post('/units/{unit}/conversions', [UnitController::class, 'storeConversion'])->name('units.conversions.store');
    Route::delete('/unit-conversions/{conversion}', [UnitController::class, 'destroyConversion'])->name('unit-conversions.destroy');
});
Route::middleware('permission:menu.view')->group(function () {
    Route::get('/menu-cycles', [MenuController::class, 'cycles'])->name('menu-cycles.index');
    Route::post('/menu-cycles', [MenuController::class, 'storeCycle'])->name('menu-cycles.store');
    Route::get('/menu-cycles/{cycle}', [MenuController::class, 'cycleShow'])->name('menu-cycles.show');
    Route::post('/menu-cycles/{cycle}/days', [MenuController::class, 'storeCycleDay'])->name('menu-cycles.days');
    Route::post('/menu-cycles/{cycle}/approve', [MenuController::class, 'approveCycle'])->name('menu-cycles.approve');
    Route::resource('menus', MenuController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/menus/{menu}/status', [MenuController::class, 'updateStatus'])->name('menus.status');
    Route::post('/menus/{menu}/nutrition', [MenuController::class, 'storeNutrition'])->name('menus.nutrition');
    Route::resource('recipes', RecipeController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/recipes/{recipe}/toggle', [RecipeController::class, 'toggleActive'])->name('recipes.toggle');
    Route::post('/recipes/{recipe}/nutrition', [RecipeController::class, 'storeNutrition'])->name('recipes.nutrition');
});

// Demand & procurement
Route::middleware('permission:demand.view')->group(function () {
    Route::get('/demands', [DemandController::class, 'index'])->name('demands.index');
    Route::get('/demands/create', [DemandController::class, 'create'])->name('demands.create');
    Route::post('/demands', [DemandController::class, 'store'])->name('demands.store');
    Route::post('/demands/generate-pr', [DemandController::class, 'generatePr'])->name('demands.generate-pr')->middleware('permission:pr.create');
    Route::delete('/demands/{demand}', [DemandController::class, 'destroy'])->name('demands.destroy');
});
Route::middleware('permission:pr.view')->group(function () {
    Route::get('/purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
    Route::get('/purchase-requests/create', [PurchaseRequestController::class, 'create'])->name('purchase-requests.create')->middleware('permission:pr.create');
    Route::post('/purchase-requests', [PurchaseRequestController::class, 'store'])->name('purchase-requests.store')->middleware('permission:pr.create');
    Route::get('/purchase-requests/{pr}', [PurchaseRequestController::class, 'show'])->name('purchase-requests.show');
    Route::post('/purchase-requests/{pr}/submit', [PurchaseRequestController::class, 'submit'])->name('purchase-requests.submit')->middleware('permission:pr.create');
    Route::post('/purchase-requests/{pr}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase-requests.approve')->middleware('permission:pr.approve');
    Route::post('/purchase-requests/{pr}/reject', [PurchaseRequestController::class, 'reject'])->name('purchase-requests.reject')->middleware('permission:pr.approve');
    Route::delete('/purchase-requests/{pr}', [PurchaseRequestController::class, 'destroy'])->name('purchase-requests.destroy');
});
Route::middleware('permission:po.view')->group(function () {
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create')->middleware('permission:po.create');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store')->middleware('permission:po.create');
    Route::get('/purchase-orders/{po}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::post('/purchase-orders/{po}/submit', [PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit')->middleware('permission:po.create');
    Route::post('/purchase-orders/{po}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve')->middleware('permission:po.approve');
    Route::post('/purchase-orders/{po}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject')->middleware('permission:po.approve');
    Route::post('/purchase-orders/{po}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel')->middleware('permission:po.approve');
});
Route::middleware('permission:gr.view')->group(function () {
    Route::get('/goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
    Route::get('/goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create')->middleware('permission:gr.create');
    Route::post('/goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store')->middleware('permission:gr.create');
    Route::get('/goods-receipts/{gr}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');
});

// Inventory
Route::middleware('permission:inventory.view')->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('/inventory/adjust', [InventoryController::class, 'adjustForm'])->name('inventory.adjust.form')->middleware('permission:inventory.adjust');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust')->middleware('permission:inventory.adjust');
    Route::get('/inventory/transfer', [InventoryController::class, 'transferForm'])->name('inventory.transfer.form')->middleware('permission:inventory.adjust');
    Route::post('/inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer')->middleware('permission:inventory.adjust');
    Route::get('/inventory/reserve', [InventoryController::class, 'reserveForm'])->name('inventory.reserve.form')->middleware('permission:inventory.adjust');
    Route::post('/inventory/reserve', [InventoryController::class, 'reserve'])->name('inventory.reserve')->middleware('permission:inventory.adjust');
    Route::post('/inventory/release', [InventoryController::class, 'release'])->name('inventory.release')->middleware('permission:inventory.adjust');
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::post('/batches/{batch}/block', [BatchController::class, 'block'])->name('batches.block')->middleware('permission:inventory.adjust');
    Route::post('/batches/{batch}/unblock', [BatchController::class, 'unblock'])->name('batches.unblock')->middleware('permission:inventory.adjust');
});
Route::middleware('permission:opname.view')->group(function () {
    Route::get('/stock-opnames', [StockOpnameController::class, 'index'])->name('stock-opnames.index');
    Route::get('/stock-opnames/create', [StockOpnameController::class, 'create'])->name('stock-opnames.create')->middleware('permission:opname.create');
    Route::post('/stock-opnames', [StockOpnameController::class, 'store'])->name('stock-opnames.store')->middleware('permission:opname.create');
    Route::get('/stock-opnames/{opname}', [StockOpnameController::class, 'show'])->name('stock-opnames.show');
    Route::post('/stock-opnames/{opname}/count', [StockOpnameController::class, 'saveCount'])->name('stock-opnames.count')->middleware('permission:opname.create');
    Route::post('/stock-opnames/{opname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opnames.approve')->middleware('permission:opname.approve');
    Route::post('/stock-opnames/{opname}/post', [StockOpnameController::class, 'post'])->name('stock-opnames.post')->middleware('permission:opname.approve');
});

// Production
Route::middleware('permission:production.view')->group(function () {
    Route::get('/production-plans', [ProductionPlanController::class, 'index'])->name('production-plans.index');
    Route::get('/production-plans/create', [ProductionPlanController::class, 'create'])->name('production-plans.create')->middleware('permission:production.create');
    Route::post('/production-plans', [ProductionPlanController::class, 'store'])->name('production-plans.store')->middleware('permission:production.create');
    Route::get('/production-plans/{plan}', [ProductionPlanController::class, 'show'])->name('production-plans.show');
    Route::post('/production-plans/{plan}/approve', [ProductionPlanController::class, 'approve'])->name('production-plans.approve')->middleware('permission:production.create');
    Route::post('/production-plans/{plan}/generate', [ProductionPlanController::class, 'generateOrders'])->name('production-plans.generate')->middleware('permission:production.create');

    Route::get('/production-orders', [ProductionOrderController::class, 'index'])->name('production-orders.index');
    Route::get('/production-orders/{order}', [ProductionOrderController::class, 'show'])->name('production-orders.show');
    Route::post('/production-orders/{order}/release', [ProductionOrderController::class, 'release'])->name('production-orders.release')->middleware('permission:production.create');
    Route::post('/production-orders/{order}/start', [ProductionOrderController::class, 'start'])->name('production-orders.start')->middleware('permission:production.create');
    Route::post('/production-orders/{order}/consume', [ProductionOrderController::class, 'consume'])->name('production-orders.consume')->middleware('permission:production.create');
    Route::post('/production-orders/{order}/complete', [ProductionOrderController::class, 'complete'])->name('production-orders.complete')->middleware('permission:production.create');
    Route::post('/production-orders/{order}/cancel', [ProductionOrderController::class, 'cancel'])->name('production-orders.cancel')->middleware('permission:production.create');
    Route::post('/production-orders/{order}/work-center', [ProductionOrderController::class, 'assignWorkCenter'])->name('production-orders.work-center')->middleware('permission:production.create');
    Route::post('/production-orders/{order}/operator', [ProductionOrderController::class, 'assignOperator'])->name('production-orders.operator')->middleware('permission:production.create');
    Route::post('/production-orders/{order}/material-check', [ProductionOrderController::class, 'materialCheck'])->name('production-orders.material-check')->middleware('permission:production.create');
    Route::post('/production-orders/{order}/downtime', [ProductionOrderController::class, 'recordDowntime'])->name('production-orders.downtime')->middleware('permission:production.create');

    Route::get('/packagings', [PackagingController::class, 'index'])->name('packagings.index');
    Route::get('/packagings/create', [PackagingController::class, 'create'])->name('packagings.create')->middleware('permission:production.create');
    Route::post('/packagings', [PackagingController::class, 'store'])->name('packagings.store')->middleware('permission:production.create');
    Route::get('/packagings/{pkg}', [PackagingController::class, 'show'])->name('packagings.show');
    Route::post('/packagings/{pkg}/complete', [PackagingController::class, 'complete'])->name('packagings.complete')->middleware('permission:production.create');
    Route::post('/packagings/{pkg}/materials', [PackagingController::class, 'useMaterial'])->name('packagings.materials')->middleware('permission:production.create');
});
Route::middleware('permission:qc.view')->group(function () {
    Route::get('/quality-controls', [QualityControlController::class, 'index'])->name('quality-controls.index');
    Route::get('/quality-controls/create', [QualityControlController::class, 'create'])->name('quality-controls.create')->middleware('permission:qc.create');
    Route::post('/quality-controls', [QualityControlController::class, 'store'])->name('quality-controls.store')->middleware('permission:qc.create');
});

// Distribution
Route::middleware('permission:distribution.view')->group(function () {
    Route::get('/distributions', [DistributionController::class, 'index'])->name('distributions.index');
    Route::get('/distributions/create', [DistributionController::class, 'create'])->name('distributions.create')->middleware('permission:distribution.create');
    Route::post('/distributions', [DistributionController::class, 'store'])->name('distributions.store')->middleware('permission:distribution.create');
    Route::get('/distributions/{dist}', [DistributionController::class, 'show'])->name('distributions.show');
    Route::post('/distributions/{dist}/dispatch', [DistributionController::class, 'dispatch'])->name('distributions.dispatch')->middleware('permission:distribution.create');
});
Route::middleware('permission:delivery.view')->group(function () {
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('/deliveries/{delivery}/deliver', [DeliveryController::class, 'deliver'])->name('deliveries.deliver')->middleware('permission:delivery.update');
    Route::post('/deliveries/{delivery}/fail', [DeliveryController::class, 'fail'])->name('deliveries.fail')->middleware('permission:delivery.update');
    Route::post('/deliveries/{delivery}/track', [DeliveryController::class, 'track'])->name('deliveries.track')->middleware('permission:delivery.update');
});

// Waste & costing
Route::middleware('permission:waste.view')->group(function () {
    Route::get('/wastes', [WasteController::class, 'index'])->name('wastes.index');
    Route::get('/wastes/create', [WasteController::class, 'create'])->name('wastes.create')->middleware('permission:waste.create');
    Route::post('/wastes', [WasteController::class, 'store'])->name('wastes.store')->middleware('permission:waste.create');
});
Route::middleware('permission:costing.view')->group(function () {
    Route::get('/costings', [CostingController::class, 'index'])->name('costings.index');
    Route::get('/costings/history', [CostingController::class, 'history'])->name('costings.history');
    Route::get('/costings/{costing}', [CostingController::class, 'show'])->name('costings.show');
});

// Reports
Route::middleware('permission:report.view')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('/reports/production', [ReportController::class, 'production'])->name('reports.production');
    Route::get('/reports/delivery', [ReportController::class, 'delivery'])->name('reports.delivery');
    Route::get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    Route::get('/reports/expiry', [ReportController::class, 'expiry'])->name('reports.expiry');
    Route::get('/reports/intelligence', [ReportController::class, 'intelligence'])->name('reports.intelligence');
    Route::get('/reports/waste', [ReportController::class, 'waste'])->name('reports.waste');
    Route::get('/reports/supplier', [ReportController::class, 'supplier'])->name('reports.supplier');
    Route::get('/reports/recall', [ReportController::class, 'recall'])->name('reports.recall');
    Route::get('/reports/nutrition', [ReportController::class, 'nutrition'])->name('reports.nutrition');
});

// Notifications
Route::get('/notifications', [AdminController::class, 'notificationIndex'])->name('notifications.index');
Route::post('/notifications/{id}/read', [AdminController::class, 'notificationRead'])->name('notifications.read');
Route::post('/notifications/read-all', [AdminController::class, 'notificationReadAll'])->name('notifications.read-all');

// Admin
Route::middleware('permission:user.view')->group(function () {
    Route::get('/users', [AdminController::class, 'userIndex'])->name('users.index');
    Route::post('/users', [AdminController::class, 'userStore'])->name('users.store');
    Route::post('/users/{user}/toggle', [AdminController::class, 'userToggle'])->name('users.toggle');
    Route::post('/users/{user}/role', [AdminController::class, 'userRole'])->name('users.role');
});
Route::middleware('permission:role.view')->group(function () {
    Route::get('/roles', [AdminController::class, 'roleIndex'])->name('roles.index');
    Route::post('/roles/{role}/permissions', [AdminController::class, 'roleSync'])->name('roles.permissions');
});
Route::middleware('permission:audit.view')->group(function () {
    Route::get('/audit-logs', [AdminController::class, 'auditIndex'])->name('audit-logs.index');
});
Route::middleware('permission:setting.view')->group(function () {
    Route::get('/settings', [AdminController::class, 'settingIndex'])->name('settings.index');
    Route::post('/settings', [AdminController::class, 'settingStore'])->name('settings.store');
});

// BOM
Route::middleware('permission:bom.view')->group(function () {
    Route::get('/boms', [BomController::class, 'index'])->name('boms.index');
    Route::get('/boms/create', [BomController::class, 'create'])->name('boms.create')->middleware('permission:bom.create');
    Route::post('/boms', [BomController::class, 'store'])->name('boms.store')->middleware('permission:bom.create');
    Route::get('/boms/{bom}', [BomController::class, 'show'])->name('boms.show');
    Route::post('/boms/{bom}/approve', [BomController::class, 'approve'])->name('boms.approve')->middleware('permission:bom.approve');
    Route::delete('/boms/{bom}', [BomController::class, 'destroy'])->name('boms.destroy')->middleware('permission:bom.create');
});

// Demand plans & MRP
Route::middleware('permission:demand.view')->group(function () {
    Route::get('/demand-plans', [DemandPlanController::class, 'index'])->name('demand-plans.index');
    Route::get('/demand-plans/create', [DemandPlanController::class, 'create'])->name('demand-plans.create');
    Route::post('/demand-plans', [DemandPlanController::class, 'store'])->name('demand-plans.store');
    Route::get('/demand-plans/{plan}', [DemandPlanController::class, 'show'])->name('demand-plans.show');
    Route::post('/demand-plans/{plan}/approve', [DemandPlanController::class, 'approve'])->name('demand-plans.approve');
});
Route::middleware('permission:mrp.view')->group(function () {
    Route::get('/mrp', [MrpController::class, 'index'])->name('mrp.index');
    Route::post('/mrp/run', [MrpController::class, 'run'])->name('mrp.run')->middleware('permission:mrp.run');
    Route::get('/mrp/{run}', [MrpController::class, 'show'])->name('mrp.show');
    Route::post('/mrp/{run}/to-pr', [MrpController::class, 'toPr'])->name('mrp.to-pr')->middleware('permission:pr.create');
});

// RFQ & Invoices
Route::middleware('permission:rfq.view')->group(function () {
    Route::get('/rfqs', [RfqController::class, 'index'])->name('rfqs.index');
    Route::get('/rfqs/create', [RfqController::class, 'create'])->name('rfqs.create')->middleware('permission:rfq.create');
    Route::post('/rfqs', [RfqController::class, 'store'])->name('rfqs.store')->middleware('permission:rfq.create');
    Route::get('/rfqs/{rfq}', [RfqController::class, 'show'])->name('rfqs.show');
    Route::post('/rfqs/{rfq}/quotations', [RfqController::class, 'storeQuotation'])->name('rfqs.quotations')->middleware('permission:rfq.create');
    Route::post('/rfqs/{rfq}/award', [RfqController::class, 'award'])->name('rfqs.award')->middleware('permission:po.create');
});
Route::middleware('permission:invoice.view')->group(function () {
    Route::get('/invoices', [SupplierInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [SupplierInvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [SupplierInvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [SupplierInvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/verify', [SupplierInvoiceController::class, 'verify'])->name('invoices.verify')->middleware('permission:invoice.verify');
    Route::post('/invoices/{invoice}/pay', [SupplierInvoiceController::class, 'markPaid'])->name('invoices.pay')->middleware('permission:invoice.verify');
});

// WMS
Route::middleware('permission:wms.view')->group(function () {
    Route::get('/wms/locations', [WmsController::class, 'locations'])->name('wms.locations');
    Route::post('/wms/zones', [WmsController::class, 'storeZone'])->name('wms.zones.store')->middleware('permission:inventory.adjust');
    Route::post('/wms/racks', [WmsController::class, 'storeRack'])->name('wms.racks.store')->middleware('permission:inventory.adjust');
    Route::post('/wms/bins', [WmsController::class, 'storeBin'])->name('wms.bins.store')->middleware('permission:inventory.adjust');
    Route::post('/wms/putaway/{batch}', [WmsController::class, 'putaway'])->name('wms.putaway')->middleware('permission:inventory.adjust');
    Route::post('/wms/quarantine/{batch}', [WmsController::class, 'quarantine'])->name('wms.quarantine')->middleware('permission:qc.create');
    Route::post('/wms/release/{batch}', [WmsController::class, 'release'])->name('wms.release')->middleware('permission:qc.create');
    Route::get('/wms/scan', [WmsController::class, 'scan'])->name('wms.scan');
});

// QMS
Route::middleware('permission:qms.view')->group(function () {
    Route::get('/inspections/templates', [QualityInspectionController::class, 'templates'])->name('inspections.templates');
    Route::post('/inspections/templates', [QualityInspectionController::class, 'storeTemplate'])->name('inspections.templates.store')->middleware('permission:qc.create');
    Route::get('/inspections', [QualityInspectionController::class, 'index'])->name('inspections.index');
    Route::get('/inspections/create', [QualityInspectionController::class, 'create'])->name('inspections.create')->middleware('permission:qc.create');
    Route::post('/inspections', [QualityInspectionController::class, 'store'])->name('inspections.store')->middleware('permission:qc.create');
    Route::get('/inspections/{inspection}', [QualityInspectionController::class, 'show'])->name('inspections.show');
    Route::post('/inspections/{inspection}/ncr', [QualityInspectionController::class, 'storeNcr'])->name('inspections.ncr')->middleware('permission:qc.create');
    Route::get('/ncrs', [QualityInspectionController::class, 'ncrs'])->name('ncrs.index');
    Route::get('/ncrs/{ncr}', [QualityInspectionController::class, 'ncrShow'])->name('ncrs.show');
    Route::post('/ncrs/{ncr}/capa', [QualityInspectionController::class, 'storeCapa'])->name('ncrs.capa')->middleware('permission:qc.create');
    Route::post('/capa/{capa}/complete', [QualityInspectionController::class, 'completeCapa'])->name('capa.complete')->middleware('permission:qc.create');
    Route::post('/ncrs/{ncr}/close', [QualityInspectionController::class, 'closeNcr'])->name('ncrs.close')->middleware('permission:qc.create');
    Route::get('/temp-logs', [QualityInspectionController::class, 'tempLogs'])->name('temp.index');
    Route::post('/temp-logs', [QualityInspectionController::class, 'storeTempLog'])->name('temp.store')->middleware('permission:qc.create');
});

// Traceability & Recall
Route::middleware('permission:inventory.view')->group(function () {
    Route::get('/trace', [TraceController::class, 'form'])->name('trace.form');
    Route::post('/trace', [TraceController::class, 'lookup'])->name('trace.lookup');
    Route::get('/trace/{batch}', [TraceController::class, 'batch'])->name('trace.batch');
});
Route::middleware('permission:recall.view')->group(function () {
    Route::get('/recalls', [RecallController::class, 'index'])->name('recalls.index');
    Route::get('/recalls/create', [RecallController::class, 'create'])->name('recalls.create')->middleware('permission:recall.create');
    Route::post('/recalls', [RecallController::class, 'store'])->name('recalls.store')->middleware('permission:recall.create');
    Route::get('/recalls/{recall}', [RecallController::class, 'show'])->name('recalls.show');
    Route::post('/recalls/{recall}/activate', [RecallController::class, 'activate'])->name('recalls.activate')->middleware('permission:recall.approve');
    Route::post('/recalls/{recall}/contain', [RecallController::class, 'contain'])->name('recalls.contain')->middleware('permission:recall.approve');
    Route::post('/recalls/{recall}/close', [RecallController::class, 'close'])->name('recalls.close')->middleware('permission:recall.approve');
});

// TMS
Route::middleware('permission:tms.view')->group(function () {
    Route::get('/tms/routes', [TmsController::class, 'routes'])->name('tms.routes');
    Route::post('/tms/routes', [TmsController::class, 'storeRoute'])->name('tms.routes.store');
    Route::get('/tms/routes/{route}', [TmsController::class, 'routeShow'])->name('tms.routes.show');
    Route::post('/tms/routes/{route}/stops', [TmsController::class, 'storeStop'])->name('tms.stops.store');
    Route::delete('/tms/stops/{stop}', [TmsController::class, 'destroyStop'])->name('tms.stops.destroy');
    Route::post('/tms/routes/{route}/apply', [TmsController::class, 'applyRoute'])->name('tms.routes.apply');
    Route::get('/tms/tower', [TmsController::class, 'controlTower'])->name('tms.tower');
});

// School portal
Route::middleware('permission:portal.view')->group(function () {
    Route::get('/portal', [PortalController::class, 'index'])->name('portal.index');
    Route::get('/portal/{delivery}', [PortalController::class, 'show'])->name('portal.show');
    Route::post('/portal/{delivery}/confirm', [PortalController::class, 'confirm'])->name('portal.confirm');
    Route::get('/portal-complaints', [PortalController::class, 'complaints'])->name('portal.complaints');
});

// Analytics & exports
Route::middleware('permission:report.view')->group(function () {
    Route::get('/analytics', [AnalyticsController::class, 'executive'])->name('analytics.executive');
    Route::get('/analytics/export/{dataset}', [AnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('/capacity', [CapacityController::class, 'index'])->name('capacity.index');
});

// Webhooks & approvals
Route::middleware('permission:webhook.view')->group(function () {
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::post('/webhooks/{webhook}/toggle', [WebhookController::class, 'toggle'])->name('webhooks.toggle');
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/webhooks/{webhook}/rotate', [WebhookController::class, 'rotateSecret'])->name('webhooks.rotate');
    Route::post('/webhook-deliveries/{delivery}/retry', [WebhookController::class, 'retry'])->name('webhooks.retry');
});
Route::middleware('permission:approval.view')->group(function () {
    Route::get('/approvals', [ApprovalController::class, 'inbox'])->name('approvals.inbox');
});

// Master catalog
Route::middleware('permission:catalog.view')->group(function () {
    Route::get('/catalog/allergens', [MasterCatalogController::class, 'allergens'])->name('catalog.allergens');
    Route::post('/catalog/allergens', [MasterCatalogController::class, 'storeAllergen'])->name('catalog.allergens.store');
    Route::delete('/catalog/allergens/{allergen}', [MasterCatalogController::class, 'destroyAllergen'])->name('catalog.allergens.destroy');
    Route::get('/catalog/meal-groups', [MasterCatalogController::class, 'mealGroups'])->name('catalog.meal-groups');
    Route::post('/catalog/meal-groups', [MasterCatalogController::class, 'storeMealGroup'])->name('catalog.meal-groups.store');
    Route::delete('/catalog/meal-groups/{group}', [MasterCatalogController::class, 'destroyMealGroup'])->name('catalog.meal-groups.destroy');
    Route::get('/catalog/vehicles', [MasterCatalogController::class, 'vehicles'])->name('catalog.vehicles');
    Route::post('/catalog/vehicles', [MasterCatalogController::class, 'storeVehicle'])->name('catalog.vehicles.store');
    Route::delete('/catalog/vehicles/{vehicle}', [MasterCatalogController::class, 'destroyVehicle'])->name('catalog.vehicles.destroy');
    Route::get('/catalog/work-centers', [MasterCatalogController::class, 'workCenters'])->name('catalog.work-centers');
    Route::post('/catalog/work-centers', [MasterCatalogController::class, 'storeWorkCenter'])->name('catalog.work-centers.store');
    Route::delete('/catalog/work-centers/{center}', [MasterCatalogController::class, 'destroyWorkCenter'])->name('catalog.work-centers.destroy');
});
