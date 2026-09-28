<?php

namespace App\Http\Controllers;

use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Http\Request;

class DocsController extends Controller
{
    /** Halaman dokumentasi publik: tutorial, hak akses, password. */
    public function index(Request $request)
    {
        $section = $request->get('s', 'tutorial');
        abort_unless(in_array($section, ['tutorial', 'akses', 'password']), 404);

        $permissions = RolesPermissionsSeeder::PERMISSIONS;
        $matrix = RolesPermissionsSeeder::ROLE_PERMS;
        $roles = array_keys($matrix);
        $accounts = [
            ['Administrator', 'admin@mbg.id', 'Akses penuh semua modul'],
            ['Procurement', 'procurement@mbg.id', 'PR, PO, RFQ, invoice, supplier'],
            ['Gudang', 'gudang@mbg.id', 'GR, stok, opname, WMS'],
            ['Dapur', 'dapur@mbg.id', 'Produksi, QC, menu'],
            ['Kurir', 'driver@mbg.id', 'Delivery + tracking'],
            ['Sekolah', 'sekolah@mbg.id', 'Portal konfirmasi & keluhan'],
        ];

        return view('docs.index', compact('section', 'permissions', 'matrix', 'roles', 'accounts'));
    }
}
