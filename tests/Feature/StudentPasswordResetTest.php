<?php

namespace Tests\Feature;

use Filament\Facades\Filament;
use Tests\TestCase;

class StudentPasswordResetTest extends TestCase
{
    public function test_students_can_open_the_password_reset_request_page(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->get('/student/password-reset/request')
            ->assertOk()
            ->assertSee('Forgot password?');
    }

    public function test_student_login_page_links_to_password_reset(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('student'));

        $this->get(Filament::getPanel('student')->getLoginUrl())
            ->assertOk()
            ->assertSee('Forgot password?');
    }
}
