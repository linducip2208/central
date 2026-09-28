<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Jobs\DeliverWebhook;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    use AuthorizesOrgAccess;

    public function index(Request $request)
    {
        $webhooks = Webhook::where('organization_id', $request->user()->organization_id)->withCount(['deliveries'])->get();
        $recent = WebhookDelivery::whereHas('webhook', fn ($q) => $q->where('organization_id', $request->user()->organization_id))->latest()->take(20)->get();
        $events = Webhook::EVENTS;

        return view('webhooks.index', compact('webhooks', 'recent', 'events'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'in:'.implode(',', Webhook::EVENTS),
        ]);
        Webhook::create([
            'organization_id' => $request->user()->organization_id,
            'name' => $data['name'], 'url' => $data['url'],
            'events' => $data['events'], 'is_active' => true,
        ]);

        return back()->with('success', 'Webhook dibuat. Secret signing tersedia di daftar (simpan aman).');
    }

    public function toggle(Webhook $webhook)
    {
        $this->ensureOrgAccess($webhook);
        $webhook->update(['is_active' => ! $webhook->is_active]);

        return back()->with('success', 'Status webhook diperbarui.');
    }

    public function destroy(Webhook $webhook)
    {
        $this->ensureOrgAccess($webhook);
        $webhook->delete();

        return back()->with('success', 'Webhook dihapus.');
    }

    public function retry(WebhookDelivery $delivery)
    {
        $this->ensureOrgAccess($delivery->webhook);
        abort_unless($delivery->canRetry(), 422, 'Delivery tidak dapat di-retry.');
        DeliverWebhook::dispatch($delivery->id);

        return back()->with('success', 'Retry dijadwalkan.');
    }

    public function rotateSecret(Webhook $webhook)
    {
        $this->ensureOrgAccess($webhook);
        $webhook->update(['secret' => bin2hex(random_bytes(24))]);

        return back()->with('success', 'Secret baru dibuat. Perbarui di sisi penerima.');
    }
}
