<?php

namespace Tests\Feature;

use Database\Seeders\RolesPermissionsSeeder;
use Tests\TestCase;

class DocsTest extends TestCase
{
    public function test_docs_public_sections(): void
    {
        $this->get('/docs')->assertOk()->assertSee('Cara memakai aplikasi');
        $this->get('/docs?s=akses')->assertOk()->assertSee('Hak akses pengguna')->assertSee('po.approve');
        $this->get('/docs?s=password')->assertOk()->assertSee('password123')->assertSee('2FA');
        $this->get('/docs?s=unknown')->assertNotFound();
    }

    public function test_docs_matrix_matches_seeder(): void
    {
        $resp = $this->get('/docs?s=akses');
        $resp->assertOk();
        foreach (RolesPermissionsSeeder::PERMISSIONS as $perm) {
            $resp->assertSee($perm, false);
        }
    }
}
