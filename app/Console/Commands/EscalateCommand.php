<?php

namespace App\Console\Commands;

use App\Models\CapaAction;
use App\Models\Delivery;
use App\Models\NonConformance;
use App\Models\Organization;
use App\Models\Rfq;
use App\Services\AutomationService;
use Illuminate\Console\Command;

class EscalateCommand extends Command
{
    protected $signature = 'mbg:escalate';

    protected $description = 'Eskalasi: CAPA overdue, delivery terlambat, NCR lama terbuka, RFQ lewat deadline.';

    public function handle(AutomationService $automation): int
    {
        $today = now()->toDateString();
        foreach (Organization::all() as $org) {
            $capa = CapaAction::where('status', '!=', 'DONE')->whereDate('due_date', '<', $today)
                ->whereHas('nonConformance', fn ($q) => $q->where('organization_id', $org->id))->count();
            if ($capa > 0) {
                $automation->fire('capa.overdue', ['organization_id' => $org->id, 'count' => $capa, 'message' => "{$capa} CAPA overdue."]);
            }
            $late = Delivery::where('organization_id', $org->id)->whereIn('status', ['PLANNED', 'IN_TRANSIT'])
                ->whereNotNull('eta')->where('eta', '<', now())->count();
            if ($late > 0) {
                $automation->fire('delivery.delayed', ['organization_id' => $org->id, 'count' => $late, 'message' => "{$late} delivery melewati ETA."]);
            }
            $ncr = NonConformance::where('organization_id', $org->id)->where('status', 'OPEN')
                ->whereDate('created_at', '<=', now()->subDays(7)->toDateString())->count();
            if ($ncr > 0) {
                $automation->fire('qc.failed', ['organization_id' => $org->id, 'count' => $ncr, 'message' => "{$ncr} NCR terbuka > 7 hari."]);
            }
            $rfq = Rfq::where('organization_id', $org->id)->where('status', 'SENT')
                ->whereDate('deadline', '<', $today)->count();
            if ($rfq > 0) {
                $automation->fire('supplier.degraded', ['organization_id' => $org->id, 'count' => $rfq, 'message' => "{$rfq} RFQ lewat deadline tanpa award."]);
            }
        }
        $this->info('Eskalasi selesai.');

        return self::SUCCESS;
    }
}
