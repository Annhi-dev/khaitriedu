<?php

namespace Tests\Feature;

use App\Models\VaiTro;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class vi_du_tinh_nang_test extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_still_open_the_public_home_page(): void
    {
        $student = NguoiDung::factory()->create([
            'role_id' => VaiTro::idByName('student'),
        ]);

        $response = $this
            ->withSession(['user_id' => $student->id])
            ->get(route('home'));

        $response->assertOk();
        $response->assertSee('Về trang cá nhân');
        $response->assertSee('KhaiTriEdu');
    }
}

