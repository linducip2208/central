<?php

namespace App\Services\Exceptions;

class InsufficientStockException extends \RuntimeException
{
    public function __construct(
        public readonly string $itemType,
        public readonly int $itemId,
        public readonly int $warehouseId,
        public readonly float $requested,
        public readonly float $available,
    ) {
        parent::__construct(sprintf(
            'Stok tidak mencukupi (%s #%d, gudang #%d): diminta %s, tersedia %s.',
            $itemType, $itemId, $warehouseId, $requested, $available
        ));
    }
}
