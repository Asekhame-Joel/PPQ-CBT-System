<?php

namespace Tests\Feature;

use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\RelationManagers\DeviceSessionsRelationManager;
use App\Models\DeviceSession;
use App\Models\User;
use App\Support\StudentDeviceManager;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class StudentDeviceManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_account_can_use_two_devices_but_a_third_device_is_rejected(): void
    {
        $student = User::factory()->create();
        $deviceManager = app(StudentDeviceManager::class);

        $deviceManager->registerOrTouch($student, $this->deviceRequest(str_repeat('a', 64), str_repeat('1', 40)));
        $deviceManager->registerOrTouch($student, $this->deviceRequest(str_repeat('b', 64), str_repeat('2', 40)));

        try {
            $deviceManager->registerOrTouch($student, $this->deviceRequest(str_repeat('c', 64), str_repeat('3', 40)));
            $this->fail('The third device should not have been registered.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'This account is already signed in on two devices. Sign out another device or contact support.',
                $exception->errors()['data.email'][0],
            );
        }

        $this->assertDatabaseCount('device_sessions', 2);
    }

    public function test_repeat_requests_from_the_same_device_do_not_consume_another_slot(): void
    {
        $student = User::factory()->create();
        $deviceManager = app(StudentDeviceManager::class);
        $deviceKey = str_repeat('a', 64);

        $first = $deviceManager->registerOrTouch(
            $student,
            $this->deviceRequest($deviceKey, str_repeat('1', 40)),
        );
        $second = $deviceManager->registerOrTouch(
            $student,
            $this->deviceRequest($deviceKey, str_repeat('2', 40)),
        );

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('device_sessions', 1);
        $this->assertSame(hash('sha256', str_repeat('2', 40)), $second->fresh()->session_id_hash);
    }

    public function test_disabled_device_cannot_sign_back_into_the_account(): void
    {
        $student = User::factory()->create();
        $administrator = User::factory()->admin()->create();
        $deviceManager = app(StudentDeviceManager::class);
        $request = $this->deviceRequest(str_repeat('a', 64), str_repeat('1', 40));
        $device = $deviceManager->registerOrTouch($student, $request);

        $deviceManager->disable($device, $administrator);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('This device has been disabled for this account.');

        $deviceManager->registerOrTouch($student, $request);
    }

    public function test_signing_out_releases_the_device_slot(): void
    {
        $student = User::factory()->create();
        $deviceManager = app(StudentDeviceManager::class);
        $request = $this->deviceRequest(str_repeat('a', 64), str_repeat('1', 40));
        $device = $deviceManager->registerOrTouch($student, $request);

        $deviceManager->signOut($student, $request);

        $this->assertNotNull($device->fresh()->signed_out_at);

        $deviceManager->registerOrTouch(
            $student,
            $this->deviceRequest(str_repeat('b', 64), str_repeat('2', 40)),
        );
        $deviceManager->registerOrTouch(
            $student,
            $this->deviceRequest(str_repeat('c', 64), str_repeat('3', 40)),
        );

        $this->assertDatabaseCount('device_sessions', 3);
    }

    public function test_admin_can_disable_and_enable_a_student_device(): void
    {
        $administrator = User::factory()->admin()->create();
        $student = User::factory()->create();
        $device = DeviceSession::factory()->for($student)->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $component = Livewire::actingAs($administrator)->test(DeviceSessionsRelationManager::class, [
            'ownerRecord' => $student,
            'pageClass' => EditStudent::class,
        ]);

        $component
            ->assertCanSeeTableRecords([$device])
            ->callAction(TestAction::make('disableDevice')->table($device))
            ->assertHasNoActionErrors();

        $device->refresh();
        $this->assertNotNull($device->disabled_at);
        $this->assertSame($administrator->id, $device->disabled_by);

        $component
            ->callAction(TestAction::make('enableDevice')->table($device))
            ->assertHasNoActionErrors();

        $this->assertNull($device->fresh()->disabled_at);
    }

    public function test_disabled_device_is_signed_out_on_its_next_student_request(): void
    {
        $student = User::factory()->create();
        $deviceKey = str_repeat('a', 64);
        DeviceSession::factory()->for($student)->create([
            'device_key_hash' => hash('sha256', $deviceKey),
            'disabled_at' => now(),
            'signed_out_at' => now(),
        ]);

        $this->actingAs($student)
            ->withCookie(StudentDeviceManager::CookieName, $deviceKey)
            ->get('/student')
            ->assertRedirect('/student/login');

        $this->assertGuest();
    }

    private function deviceRequest(string $deviceKey, string $sessionId): Request
    {
        $request = Request::create(
            uri: '/student',
            method: 'GET',
            cookies: [StudentDeviceManager::CookieName => $deviceKey],
            server: [
                'REMOTE_ADDR' => '203.0.113.10',
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 Chrome/140.0 Safari/537.36',
            ],
        );
        $request->setLaravelSession(new Store(
            'examforge-test',
            new ArraySessionHandler(120),
            $sessionId,
        ));

        return $request;
    }
}
