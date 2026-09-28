<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        $imports = Import::where('organization_id', $request->user()->organization_id)->latest()->take(20)->get();

        return view('imports.index', compact('imports'));
    }

    public function preview(Request $request, ImportService $imports, string $entity)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        abort_unless(in_array($entity, $imports->entities()), 404);
        try {
            $result = $imports->preview($request->file('file'), $entity, $request->user()->organization_id);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        // Simpan salinan untuk commit (dry-run → commit tanpa upload ulang).
        $tmp = 'import-tmp/'.uniqid('imp_', true).'.csv';
        Storage::disk('local')->put($tmp, file_get_contents($request->file('file')->getRealPath()));

        return view('imports.preview', compact('result', 'entity', 'tmp'));
    }

    public function commit(Request $request, ImportService $imports, string $entity)
    {
        $request->validate(['tmp' => 'required|string|max:100']);
        abort_unless(in_array($entity, $imports->entities()), 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($request->tmp) && str_starts_with($request->tmp, 'import-tmp/'), 422, 'File preview kedaluwarsa. Ulangi preview.');
        $upload = new UploadedFile($disk->path($request->tmp), basename($request->tmp), 'text/csv', null, true);
        try {
            $import = $imports->commit($upload, $entity, $request->user()->organization_id, $request->user()->id);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        } finally {
            $disk->delete($request->tmp);
        }

        return redirect()->route('imports.index')->with('success', "Import {$entity}: {$import->imported_rows} berhasil, {$import->failed_rows} gagal.");
    }
}
