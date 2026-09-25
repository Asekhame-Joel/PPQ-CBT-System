<?php

namespace Tests\Feature\Admin;

use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\RecentPayments;
use App\Filament\Widgets\RevenueTrend;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use ReflectionMethod;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_statistics_show_current_platform_totals(): void
    {
        $this->travelTo('2026-09-25 12:00:00');
        $admin = User::factory()->admin()->create();
        User::factory()->count(2)->create();
        User::factory()->inactive()->create();
        $activeCourse = Course::factory()->create();
        Course::factory()->inactive()->create();
        Question::factory()->count(3)->for($activeCourse)->create();
        Question::factory()->inactive()->for($activeCourse)->create();
        Payment::factory()->successful()->create(['amount' => '2500.00']);
        Payment::factory()->successful()->create([
            'amount' => '9000.00',
            'paid_at' => now()->subMonth(),
        ]);
        Payment::factory()->create(['amount' => '8000.00']);
        QuizAttempt::factory()->count(2)->submitted()->create();
        QuizAttempt::factory()->create(['started_at' => now()->subDay()]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(AdminStatsOverview::class)
            ->assertSee('Active students')
            ->assertSee('2')
            ->assertSee('Active courses')
            ->assertSee('3 active questions')
            ->assertSee('Revenue this month')
            ->assertSee('2,500.00')
            ->assertSee('Practice today')
            ->assertSee('2 completed today');
    }

    public function test_revenue_chart_returns_seven_days_with_only_successful_payment_totals(): void
    {
        $this->travelTo('2026-09-25 12:00:00');
        Payment::factory()->successful()->create(['amount' => '1000.00', 'paid_at' => now()->subDays(2)]);
        Payment::factory()->successful()->create(['amount' => '500.00', 'paid_at' => now()->subDays(2)]);
        Payment::factory()->create(['amount' => '8000.00', 'created_at' => now()->subDays(2)]);
        Payment::factory()->successful()->create(['amount' => '9000.00', 'paid_at' => now()->subDays(10)]);

        $method = new ReflectionMethod(RevenueTrend::class, 'getData');
        $data = $method->invoke(app(RevenueTrend::class));

        $this->assertCount(7, $data['labels']);
        $this->assertSame('Wed, Sep 23', $data['labels'][4]);
        $this->assertSame(1500.0, $data['datasets'][0]['data'][4]);
        $this->assertSame(1500.0, array_sum($data['datasets'][0]['data']));
    }

    public function test_recent_payments_widget_is_limited_to_the_latest_five_records(): void
    {
        $admin = User::factory()->admin()->create();
        $payments = collect();

        foreach (range(1, 6) as $day) {
            $payments->push(Payment::factory()->create(['created_at' => now()->subDays($day)]));
        }

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(RecentPayments::class)
            ->assertCanSeeTableRecords($payments->take(5))
            ->assertCanNotSeeTableRecords([$payments->last()]);
    }

    public function test_student_cannot_open_admin_dashboard(): void
    {
        $student = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($student)
            ->get('/admin')
            ->assertForbidden();
    }
}
