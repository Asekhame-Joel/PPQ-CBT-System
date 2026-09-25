<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_presents_the_core_student_journey(): void
    {
        $this->get('/')
            ->assertSuccessful()
            ->assertSee('ExamForge')
            ->assertSee('Practise past questions with confidence.')
            ->assertSee('Start practising')
            ->assertSee('/student/register', escape: false)
            ->assertSee('/student/login', escape: false)
            ->assertSee('/admin/login', escape: false);
    }
}
