<?php

namespace App\Services;

use App\Models\CentralKitchen;
use App\Models\Import;
use App\Models\Ingredient;
use App\Models\School;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Generic CSV import engine: mapping kolom → validasi → preview/dry-run
 * → commit transaksional → riwayat + laporan error. Rollback = hapus batch
 * via import_id bila entitas mendukungnya (tercatat di errors).
 */
class ImportService
{
    /** Definisi entitas: header, rules per kolom, factory callback. */
    protected function definitions(): array
    {
        return [
            'recipients' => [
                'header' => ['school_code', 'name', 'identifier', 'grade', 'class', 'gender', 'allergy'],
                'handler' => fn (array $row, int $orgId) => $this->importRecipient($row, $orgId),
            ],
            'ingredients' => [
                'header' => ['code', 'name', 'category', 'unit_code', 'price', 'min_stock'],
                'handler' => fn (array $row, int $orgId) => $this->importIngredient($row, $orgId),
            ],
            'schools' => [
                'header' => ['code', 'name', 'level', 'target_portions', 'district'],
                'handler' => fn (array $row, int $orgId) => $this->importSchool($row, $orgId),
            ],
        ];
    }

    public function entities(): array
    {
        return array_keys($this->definitions());
    }

    public function headerFor(string $entity): array
    {
        abort_unless(isset($this->definitions()[$entity]), 404);

        return $this->definitions()[$entity]['header'];
    }

    /** Parse + validasi tanpa menyimpan. */
    public function preview(UploadedFile $file, string $entity, int $orgId): array
    {
        $rows = $this->readRows($file, $entity);
        $valid = 0;
        $errors = [];
        foreach ($rows as $i => $row) {
            $err = $this->validateRow($entity, $row, $orgId);
            if ($err) {
                $errors[] = 'Baris '.($i + 2).': '.$err;
            } else {
                $valid++;
            }
        }

        return ['total' => count($rows), 'valid' => $valid, 'errors' => array_slice($errors, 0, 20), 'error_count' => count($errors), 'rows' => array_slice($rows, 0, 10)];
    }

    /** Commit transaksional (all-or-nothing per baris valid; baris gagal dilewati + dilaporkan). */
    public function commit(UploadedFile $file, string $entity, int $orgId, ?int $userId = null): Import
    {
        $rows = $this->readRows($file, $entity);
        $import = Import::create([
            'organization_id' => $orgId, 'entity' => $entity, 'filename' => $file->getClientOriginalName(),
            'status' => 'PROCESSING', 'total_rows' => count($rows), 'created_by' => $userId ?? Auth::id(),
        ]);
        $ok = 0;
        $errors = [];
        DB::transaction(function () use ($rows, $entity, $orgId, &$ok, &$errors) {
            foreach ($rows as $i => $row) {
                $err = $this->validateRow($entity, $row, $orgId);
                if ($err) {
                    $errors[] = 'Baris '.($i + 2).': '.$err;

                    continue;
                }
                try {
                    ($this->definitions()[$entity]['handler'])($row, $orgId);
                    $ok++;
                } catch (\Throwable $e) {
                    $errors[] = 'Baris '.($i + 2).': '.$e->getMessage();
                }
            }
        });
        $import->update(['status' => 'COMPLETED', 'imported_rows' => $ok, 'failed_rows' => count($errors), 'errors' => array_slice($errors, 0, 100)]);

        return $import->fresh();
    }

    protected function readRows(UploadedFile $file, string $entity): array
    {
        $expected = $this->headerFor($entity);
        $handle = fopen($file->getRealPath(), 'r');
        $header = array_map('strtolower', fgetcsv($handle) ?: []);
        if ($header !== $expected) {
            fclose($handle);
            throw new \InvalidArgumentException('Header harus: '.implode(',', $expected));
        }
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $rows[] = array_combine($expected, array_pad($row, count($expected), ''));
            if (count($rows) >= 5000) {
                break;
            }
        }
        fclose($handle);

        return $rows;
    }

    protected function validateRow(string $entity, array $row, int $orgId): ?string
    {
        return match ($entity) {
            'recipients' => $this->validateRecipient($row, $orgId),
            'ingredients' => $this->validateIngredient($row, $orgId),
            'schools' => $this->validateSchool($row, $orgId),
            default => 'Entitas tidak dikenal.',
        };
    }

    protected function validateRecipient(array $row, int $orgId): ?string
    {
        if (trim($row['name']) === '') {
            return 'nama wajib.';
        }
        $school = School::where('code', trim($row['school_code']))->first();
        if (! $school || (int) $school->organization_id !== $orgId) {
            return 'sekolah tidak dikenal.';
        }
        $g = strtoupper(trim($row['gender']));
        if ($g !== '' && ! in_array($g, ['L', 'P'])) {
            return 'gender harus L/P.';
        }

        return null;
    }

    protected function importRecipient(array $row, int $orgId): void
    {
        $school = School::where('code', trim($row['school_code']))->firstOrFail();
        $g = strtoupper(trim($row['gender']));
        $school->recipients()->create([
            'name' => trim($row['name']), 'identifier' => trim($row['identifier']) ?: null,
            'grade' => trim($row['grade']) ?: null, 'class_name' => trim($row['class']) ?: null,
            'gender' => in_array($g, ['L', 'P']) ? $g : null,
            'allergy_notes' => trim($row['allergy']) ?: null, 'is_active' => true,
        ]);
    }

    protected function validateIngredient(array $row, int $orgId): ?string
    {
        if (trim($row['code']) === '' || trim($row['name']) === '') {
            return 'code & name wajib.';
        }
        if (Ingredient::where('code', trim($row['code']))->exists()) {
            return 'code duplikat: '.$row['code'];
        }
        if (! Unit::where('code', trim($row['unit_code']))->exists()) {
            return 'unit tidak dikenal: '.$row['unit_code'];
        }

        return null;
    }

    protected function importIngredient(array $row, int $orgId): void
    {
        $unit = Unit::where('code', trim($row['unit_code']))->firstOrFail();
        Ingredient::create([
            'organization_id' => $orgId, 'code' => trim($row['code']), 'name' => trim($row['name']),
            'category' => in_array(strtoupper($row['category']), ['STAPLE', 'PROTEIN', 'VEGETABLE', 'FRUIT', 'SPICE', 'OIL', 'OTHER', 'PACKAGING']) ? strtoupper($row['category']) : 'OTHER',
            'unit_id' => $unit->id, 'standard_price' => (float) $row['price'],
            'min_stock' => (float) $row['min_stock'], 'is_active' => true,
        ]);
    }

    protected function validateSchool(array $row, int $orgId): ?string
    {
        if (trim($row['code']) === '' || trim($row['name']) === '') {
            return 'code & name wajib.';
        }
        if (School::where('code', trim($row['code']))->exists()) {
            return 'code duplikat.';
        }

        return null;
    }

    protected function importSchool(array $row, int $orgId): void
    {
        $ck = CentralKitchen::where('organization_id', $orgId)->firstOrFail();
        School::create([
            'organization_id' => $orgId, 'central_kitchen_id' => $ck->id,
            'code' => trim($row['code']), 'name' => trim($row['name']),
            'level' => in_array(strtoupper($row['level']), ['PAUD', 'TK', 'SD', 'SMP', 'SMA', 'SMK', 'SLB']) ? strtoupper($row['level']) : 'SD',
            'target_portions' => (int) $row['target_portions'], 'student_count' => (int) $row['target_portions'],
            'district' => trim($row['district']) ?: null, 'status' => 'ACTIVE',
        ]);
    }
}
