<?php

namespace App\Services;

use App\Models\PeriodClosing;
use Carbon\Carbon;

/**
 * Period closing: transaksi bertanggal < closed_before dikunci.
 * Dipanggil di semua titik posting stok/keuangan.
 */
class PeriodService
{
    public function closedBefore(int $organizationId, ?int $kitchenId = null): ?string
    {
        $global = PeriodClosing::where('organization_id', $organizationId)->whereNull('central_kitchen_id')->max('closed_before');
        $scoped = $kitchenId
            ? PeriodClosing::where('organization_id', $organizationId)->where('central_kitchen_id', $kitchenId)->max('closed_before')
            : null;

        return collect([$global, $scoped])->filter()->max();
    }

    public function assertOpen(int $organizationId, ?int $kitchenId, string|\DateTimeInterface $date): void
    {
        $dateStr = $date instanceof \DateTimeInterface ? Carbon::instance($date)->toDateString() : substr((string) $date, 0, 10);
        $closed = $this->closedBefore($organizationId, $kitchenId);
        if ($closed && $dateStr < $closed) {
            throw new \RuntimeException("Periode sudah ditutup (terkunci sebelum {$closed}). Tanggal {$dateStr} tidak dapat diposting.");
        }
    }

    public function close(int $organizationId, ?int $kitchenId, string $closedBefore, ?int $userId = null): PeriodClosing
    {
        return PeriodClosing::create([
            'organization_id' => $organizationId, 'central_kitchen_id' => $kitchenId,
            'closed_before' => $closedBefore, 'created_by' => $userId,
        ]);
    }
}
