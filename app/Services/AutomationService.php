<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Rule engine WHEN(event) → IF(conditions) → THEN(notify_role|webhook).
 * Dievaluasi dari listener/command; tidak menjalankan operasi berat sinkron.
 */
class AutomationService
{
    public function __construct(protected WebhookService $webhooks) {}

    /**
     * @param  array{organization_id?:int, severity?:string, ref?:string, message?:string}  $context
     * @return int jumlah aturan yang fire
     */
    public function fire(string $event, array $context = []): int
    {
        $fired = 0;
        $rules = AutomationRule::where('event', $event)->where('is_active', true)
            ->when(isset($context['organization_id']), fn ($q) => $q->where('organization_id', $context['organization_id']))
            ->get();
        foreach ($rules as $rule) {
            if (! $this->conditionsMet($rule, $context)) {
                continue;
            }
            try {
                $this->execute($rule, $context, $event);
                $rule->update(['last_fired_at' => now()]);
                $fired++;
            } catch (\Throwable $e) {
                Log::warning('Automation rule failed', ['rule' => $rule->id, 'error' => $e->getMessage()]);
            }
        }

        return $fired;
    }

    protected function conditionsMet(AutomationRule $rule, array $context): bool
    {
        foreach ($rule->conditions ?? [] as $key => $expected) {
            $actual = $context[$key] ?? null;
            if (is_array($expected) && isset($expected['min']) && $actual < $expected['min']) {
                return false;
            }
            if (is_array($expected) && isset($expected['max']) && $actual > $expected['max']) {
                return false;
            }
            if (! is_array($expected) && $actual != $expected) {
                return false;
            }
        }

        return true;
    }

    protected function execute(AutomationRule $rule, array $context, string $event): void
    {
        $message = $rule->message ?? "Otomatisasi: {$event}";
        foreach ($context as $k => $v) {
            if (! is_scalar($v)) {
                continue;
            }
            $message = str_replace('{{'.$k.'}}', (string) $v, $message);
        }
        if ($rule->action === 'notify_role' && $rule->target_role) {
            $users = User::active()
                ->when(isset($context['organization_id']), fn ($q) => $q->where('organization_id', $context['organization_id']))
                ->whereHas('roles', fn ($q) => $q->where('name', $rule->target_role))
                ->get();
            app(TemplatedNotifier::class)->sendRaw($users, $event, $message);
        } elseif ($rule->action === 'webhook') {
            $this->webhooks->dispatch('automation.fired', ['rule' => $rule->name, 'event' => $event] + $context, $context['organization_id'] ?? null);
        }
    }
}
