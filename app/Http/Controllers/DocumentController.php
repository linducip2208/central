<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Document;
use App\Services\ApprovalService;
use App\Services\NumberService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Document::where('organization_id', $request->user()->organization_id);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        $documents = $this->tableQuery($request, $query, ['code', 'title']);

        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.form', ['document' => new Document]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'category' => 'required|in:SOP,WORK_INSTRUCTION,CHECKLIST,POLICY,FORM',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'version' => 'nullable|string|max:10',
            'effective_from' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:effective_from',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
        $doc = Document::create([
            'organization_id' => $request->user()->organization_id,
            'category' => $data['category'], 'code' => $numbers->next('DOC'),
            'title' => $data['title'], 'content' => $data['content'],
            'version' => $data['version'] ?? '1.0',
            'effective_from' => $data['effective_from'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => 'DRAFT',
            'attachment_path' => $request->hasFile('attachment') ? $request->file('attachment')->store('documents', 'public') : null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('documents.show', $doc)->with('success', 'Dokumen dibuat sebagai DRAFT.');
    }

    public function show(Document $document)
    {
        $this->ensureOrgAccess($document);
        $acked = $document->acknowledgements()->where('user_id', request()->user()->id)->exists();

        return view('documents.show', compact('document', 'acked'));
    }

    public function submitReview(Document $document)
    {
        $this->ensureOrgAccess($document);
        abort_unless($document->status === 'DRAFT', 422);
        $document->update(['status' => 'REVIEW']);

        return back()->with('success', 'Dokumen dikirim untuk review.');
    }

    public function approve(Request $request, Document $document, ApprovalService $approvals)
    {
        $this->ensureOrgAccess($document);
        abort_unless(in_array($document->status, ['REVIEW', 'DRAFT']), 422);
        $document->update(['status' => 'APPROVED', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
        $approvals->decide($document, 'APPROVE');

        return back()->with('success', 'Dokumen disetujui.');
    }

    public function publish(Document $document)
    {
        $this->ensureOrgAccess($document);
        abort_unless($document->status === 'APPROVED', 422);
        $document->update(['status' => 'PUBLISHED']);

        return back()->with('success', 'Dokumen dipublikasikan.');
    }

    public function archive(Document $document)
    {
        $this->ensureOrgAccess($document);
        $document->update(['status' => 'ARCHIVED']);

        return back()->with('success', 'Dokumen diarsipkan.');
    }

    public function acknowledge(Document $document)
    {
        $this->ensureOrgAccess($document);
        abort_unless($document->isPublished(), 422, 'Hanya dokumen published yang dapat diakui.');
        $document->acknowledgements()->firstOrCreate(['user_id' => request()->user()->id], ['acknowledged_at' => now()]);

        return back()->with('success', 'Terima kasih. Pengakuan Anda tercatat.');
    }
}
