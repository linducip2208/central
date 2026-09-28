<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Delivery;
use App\Models\Document;
use App\Models\GoodsReceipt;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\Recipient;
use App\Models\School;
use App\Models\SchoolConfirmation;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Global search lintas entitas, selalu ter-skup organisasi + izin baca modul.
 */
class GlobalSearchService
{
    public function search(User $user, string $q, int $limit = 6): array
    {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return [];
        }
        $orgId = $user->organization_id;
        $like = "%{$q}%";
        $groups = [];

        $can = fn (string $perm) => $user->can($perm) || $user->hasRole(['super-admin', 'admin']);

        if ($can('school.view')) {
            $groups['Sekolah'] = School::where('organization_id', $orgId)->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('code', 'like', $like))->take($limit)->get()->map(fn ($s) => ['label' => $s->name, 'sub' => $s->code, 'url' => route('schools.show', $s)])->toArray();
            $groups['Penerima'] = Recipient::whereHas('school', fn ($w) => $w->where('organization_id', $orgId))->where('name', 'like', $like)->take($limit)->get()->map(fn ($r) => ['label' => $r->name, 'sub' => $r->school->name ?? '', 'url' => route('recipients.index', ['q' => $r->name])])->toArray();
        }
        if ($can('supplier.view')) {
            $groups['Supplier'] = Supplier::where('organization_id', $orgId)->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('code', 'like', $like))->take($limit)->get()->map(fn ($s) => ['label' => $s->name, 'sub' => $s->code, 'url' => route('suppliers.show', $s)])->toArray();
        }
        if ($can('product.view')) {
            $groups['Produk'] = Product::where('organization_id', $orgId)->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('code', 'like', $like))->take($limit)->get()->map(fn ($p) => ['label' => $p->name, 'sub' => $p->code, 'url' => route('products.show', $p)])->toArray();
            $groups['Bahan'] = Ingredient::where('organization_id', $orgId)->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('code', 'like', $like))->take($limit)->get()->map(fn ($p) => ['label' => $p->name, 'sub' => $p->code, 'url' => route('ingredients.show', $p)])->toArray();
            $groups['Batch'] = Batch::where('organization_id', $orgId)->where('batch_no', 'like', $like)->take($limit)->get()->map(fn ($b) => ['label' => $b->batch_no, 'sub' => $b->status, 'url' => route('trace.batch', $b)])->toArray();
        }
        if ($can('po.view')) {
            $groups['Purchase Order'] = PurchaseOrder::where('organization_id', $orgId)->where('number', 'like', $like)->take($limit)->get()->map(fn ($p) => ['label' => $p->number, 'sub' => $p->status, 'url' => route('purchase-orders.show', $p)])->toArray();
            $groups['Goods Receipt'] = GoodsReceipt::where('organization_id', $orgId)->where('number', 'like', $like)->take($limit)->get()->map(fn ($p) => ['label' => $p->number, 'sub' => $p->status, 'url' => route('goods-receipts.show', $p)])->toArray();
            $groups['Invoice'] = SupplierInvoice::where('organization_id', $orgId)->where(fn ($w) => $w->where('number', 'like', $like)->orWhere('supplier_invoice_no', 'like', $like))->take($limit)->get()->map(fn ($p) => ['label' => $p->number, 'sub' => $p->match_status, 'url' => route('invoices.show', $p)])->toArray();
        }
        if ($can('production.view')) {
            $groups['Production Order'] = ProductionOrder::where('organization_id', $orgId)->where('number', 'like', $like)->take($limit)->get()->map(fn ($p) => ['label' => $p->number, 'sub' => $p->status, 'url' => route('production-orders.show', $p)])->toArray();
        }
        if ($can('delivery.view')) {
            $groups['Delivery'] = Delivery::where('organization_id', $orgId)->where('number', 'like', $like)->take($limit)->get()->map(fn ($p) => ['label' => $p->number, 'sub' => $p->status, 'url' => route('deliveries.show', $p)])->toArray();
        }
        if ($can('portal.view')) {
            $groups['Keluhan'] = SchoolConfirmation::whereNotNull('complaint')->where('complaint', 'like', $like)->whereHas('school', fn ($w) => $w->where('organization_id', $orgId))->take($limit)->get()->map(fn ($p) => ['label' => Str::limit($p->complaint, 50), 'sub' => $p->school->name ?? '', 'url' => route('portal.complaints')])->toArray();
        }
        if ($can('setting.view')) {
            $groups['Dokumen'] = Document::where('organization_id', $orgId)->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('code', 'like', $like))->take($limit)->get()->map(fn ($p) => ['label' => $p->title, 'sub' => $p->code, 'url' => route('documents.show', $p)])->toArray();
        }

        return array_filter($groups, fn ($g) => ! empty($g));
    }
}
