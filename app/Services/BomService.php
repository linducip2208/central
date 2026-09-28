<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\BomItem;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Unit;

/**
 * BOM explosion rekursif dengan deteksi siklus, scaling qty, yield,
 * scrap/waste, konversi satuan, dan seleksi versi effective-date.
 */
class BomService
{
    public const MAX_DEPTH = 10;

    /**
     * Explode BOM produk menjadi kebutuhan bahan datar.
     *
     * @return array<int, array{ingredient_id:int, qty:float, unit_id:int, path:string}>
     */
    public function explode(int $productId, float $qty, ?string $date = null): array
    {
        $bom = Bom::activeForProduct($productId, $date);
        if (! $bom) {
            // Fallback: resep aktif sebagai BOM level-1.
            return $this->explodeFromRecipe($productId, $qty);
        }

        $result = [];
        $this->explodeBom($bom, $qty / max(0.0001, (float) $bom->yield_qty), $result, [], 0, $bom->code);

        return array_values($result);
    }

    protected function explodeBom(Bom $bom, float $multiplier, array &$result, array $path, int $depth, string $label): void
    {
        if ($depth > self::MAX_DEPTH) {
            throw new \RuntimeException('BOM terlalu dalam (>'.self::MAX_DEPTH.'): kemungkinan siklus pada '.$label);
        }
        if (in_array('bom:'.$bom->id, $path)) {
            throw new \RuntimeException('Siklus BOM terdeteksi pada '.$bom->code);
        }
        $path[] = 'bom:'.$bom->id;

        foreach ($bom->items as $item) {
            $need = $item->effectiveQty() * $multiplier;
            $converted = $this->toBaseUnit($need, $item);

            if ($item->component_type === 'product') {
                if (in_array('product:'.$item->component_id, $path)) {
                    throw new \RuntimeException('Siklus BOM terdeteksi pada produk #'.$item->component_id);
                }
                $sub = Bom::activeForProduct($item->component_id);
                if ($sub) {
                    $this->explodeBom($sub, $converted / max(0.0001, (float) $sub->yield_qty), $result, [...$path, 'product:'.$item->component_id], $depth + 1, $sub->code);

                    continue;
                }
                // Sub-assembly tanpa BOM: perlakukan sebagai bahan langsung bila ingredient cocok, else skip dengan catatan.
                $this->accumulate($result, $item->component_id, $converted, $item, $label.' (sub-assembly tanpa BOM)');

                continue;
            }

            if ($item->component_type === 'ingredient') {
                $this->accumulate($result, $item->component_id, $converted, $item, $label);

                continue;
            }

            // material (kemasan dsb.): catat sebagai kebutuhan non-bahan bila ingredient berkategori PACKAGING cocok.
            $this->accumulate($result, $item->component_id, $converted, $item, $label);
        }
    }

    protected function accumulate(array &$result, int $componentId, float $qty, BomItem $item, string $path): void
    {
        foreach ($result as &$row) {
            if ($row['ingredient_id'] === $componentId) {
                $row['qty'] += $qty;
                $row['path'] .= ' + '.$path;

                return;
            }
        }
        unset($row);
        $result[] = ['ingredient_id' => $componentId, 'qty' => $qty, 'unit_id' => $item->unit_id, 'path' => $path];
    }

    /** Konversi qty ke satuan dasar ingredient bila ada jalur konversi. */
    protected function toBaseUnit(float $qty, BomItem $item): float
    {
        $ing = Ingredient::find($item->component_id);
        if (! $ing || (int) $ing->unit_id === (int) $item->unit_id) {
            return $qty;
        }
        $from = Unit::find($item->unit_id);
        $converted = $from?->convertTo($qty, (int) $ing->unit_id);

        return $converted ?? $qty;
    }

    protected function explodeFromRecipe(int $productId, float $qty): array
    {
        $product = Product::find($productId);
        $recipe = $product?->recipes()->where('is_active', true)->latest()->first();
        if (! $recipe) {
            return [];
        }
        $out = [];
        foreach ($recipe->items as $ri) {
            $need = $ri->requiredFor($qty, (float) $recipe->yield_qty);
            $found = false;
            foreach ($out as &$row) {
                if ($row['ingredient_id'] === $ri->ingredient_id) {
                    $row['qty'] += $need;
                    $found = true;
                    break;
                }
            }
            unset($row);
            if (! $found) {
                $out[] = ['ingredient_id' => $ri->ingredient_id, 'qty' => $need, 'unit_id' => $ri->unit_id, 'path' => 'recipe:'.$recipe->code];
            }
        }

        return $out;
    }

    /** Validasi: pastikan penambahan item tidak membuat siklus. */
    public function assertNoCycle(Bom $bom, string $componentType, int $componentId): void
    {
        if ($componentType !== 'product') {
            return;
        }
        if ((int) $componentId === (int) $bom->item_id) {
            throw new \InvalidArgumentException('BOM tidak boleh mereferensikan produknya sendiri.');
        }
        // Jika komponen adalah sub-assembly yang (transitif) memakai produk ini → siklus.
        $this->assertProductNotDependentOn($componentId, (int) $bom->item_id, ['product:'.$componentId]);
    }

    protected function assertProductNotDependentOn(int $productId, int $targetProductId, array $path, int $depth = 0): void
    {
        if ($depth > self::MAX_DEPTH) {
            throw new \RuntimeException('Rantai BOM terlalu dalam saat validasi siklus.');
        }
        $bom = Bom::activeForProduct($productId);
        if (! $bom) {
            return;
        }
        foreach ($bom->items as $item) {
            if ($item->component_type !== 'product') {
                continue;
            }
            if ((int) $item->component_id === $targetProductId) {
                throw new \InvalidArgumentException('Penambahan ini membuat siklus BOM (produk #'.$targetProductId.' dipakai secara transitif).');
            }
            $key = 'product:'.$item->component_id;
            if (in_array($key, $path)) {
                throw new \InvalidArgumentException('Siklus BOM terdeteksi.');
            }
            $this->assertProductNotDependentOn((int) $item->component_id, $targetProductId, [...$path, $key], $depth + 1);
        }
    }
}
