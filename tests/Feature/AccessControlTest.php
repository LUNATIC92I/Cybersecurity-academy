<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class AccessControlTest extends TestCase
{
    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->assertStatus(302, $this->get('/dashboard'));
    }

    public function test_student_cannot_reach_admin(): void
    {
        $this->actingAs($this->makeUser('student'));
        $this->assertStatus(403, $this->get('/admin'));
    }

    public function test_admin_can_reach_admin(): void
    {
        $this->actingAs($this->makeUser('super_admin'));
        $this->assertStatus(200, $this->get('/admin'));
    }

    public function test_instructor_cannot_manage_commerce(): void
    {
        $this->actingAs($this->makeUser('instructor'));
        $this->assertStatus(403, $this->get('/admin/commerce'));
    }

    public function test_instructor_can_manage_content(): void
    {
        $this->actingAs($this->makeUser('instructor'));
        $this->assertStatus(200, $this->get('/admin/courses'));
    }

    public function test_premium_course_requires_access(): void
    {
        $user = $this->actingAs($this->makeUser('student'));
        $course = db()->fetch("SELECT * FROM courses WHERE slug = 'penetration-testing-professional'");
        $this->assertFalse(\App\Services\AccessService::enroll((int) $user['id'], $course), 'A student without access must not enrol in a premium course.');
        $free = db()->fetch("SELECT * FROM courses WHERE slug = 'soc-analyst-professional'");
        $this->assertTrue(\App\Services\AccessService::enroll((int) $user['id'], $free), 'The free SOC course must be enrollable.');
    }
}
