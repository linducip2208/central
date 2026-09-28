<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationCenterController extends Controller
{
    public function templates()
    {
        $templates = NotificationTemplate::orderBy('type')->get();

        return view('notifications.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|max:60',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        NotificationTemplate::updateOrCreate(['type' => $data['type']], $data + ['mail_enabled' => $request->boolean('mail_enabled')]);

        return back()->with('success', 'Template tersimpan. Variabel: {{nama}} dsb.');
    }

    public function preferences(Request $request)
    {
        $types = NotificationTemplate::orderBy('type')->pluck('type')->all();
        $prefs = NotificationPreference::where('user_id', $request->user()->id)->get()->keyBy('type');

        return view('notifications.preferences', compact('types', 'prefs'));
    }

    public function savePreferences(Request $request)
    {
        $types = NotificationTemplate::orderBy('type')->pluck('type')->all();
        foreach ($types as $type) {
            NotificationPreference::updateOrCreate(
                ['user_id' => $request->user()->id, 'type' => $type],
                ['in_app' => $request->boolean("in_app.{$type}", true), 'mail' => $request->boolean("mail.{$type}")]
            );
        }

        return back()->with('success', 'Preferensi tersimpan.');
    }
}
