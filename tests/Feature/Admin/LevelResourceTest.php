<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Levels\Pages\CreateLevel;
use App\Filament\Resources\Levels\Pages\EditLevel;
use App\Filament\Resources\Levels\Pages\ListLevels;
use App\Models\Level;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LevelResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_list_levels(): void
    {
        $admin = User::factory()->admin()->create();
        $levels = Level::factory()->count(3)->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ListLevels::class)
            ->assertCanSeeTableRecords($levels);
    }

    public function test_admin_can_create_level(): void
    {
        $admin = User::factory()->admin()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateLevel::class)
            ->fillForm([
                'name' => '100 Level',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('levels', [
            'name' => '100 Level',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_level_without_triggering_its_unique_name_rule(): void
    {
        $admin = User::factory()->admin()->create();
        $level = Level::factory()->create(['name' => '100 Level']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(EditLevel::class, ['record' => $level->id])
            ->fillForm([
                'name' => '100 Level',
                'sort_order' => 2,
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('levels', [
            'id' => $level->id,
            'sort_order' => 2,
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_create_duplicate_level_name(): void
    {
        $admin = User::factory()->admin()->create();
        Level::factory()->create(['name' => '100 Level']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(CreateLevel::class)
            ->fillForm([
                'name' => '100 Level',
                'sort_order' => 2,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);

        $this->assertSame(1, Level::where('name', '100 Level')->count());
    }
}
