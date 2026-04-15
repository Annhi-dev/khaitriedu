<?php

namespace Tests\Feature;

use Tests\TestCase;

class bao_mat_he_thong_test extends TestCase
{
    public function test_unsafe_maintenance_scripts_are_not_present(): void
    {
        $this->assertFileDoesNotExist(base_path('check.php'));
        $this->assertFileDoesNotExist(base_path('send_test.php'));
        $this->assertFileDoesNotExist(public_path('run_migration.php'));
    }

    public function test_image_assets_use_the_clear_images_directory(): void
    {
        $this->assertDirectoryExists(base_path('images'));
        $this->assertDirectoryDoesNotExist(base_path('hinh'));
        $this->assertDirectoryExists(public_path('images'));
        $this->assertDirectoryDoesNotExist(public_path('hinh'));
    }
}

