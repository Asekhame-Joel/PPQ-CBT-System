<?php

namespace App\Filament\Student\Pages;

use App\Enums\SupportRequestCategory;
use App\Enums\SupportRequestStatus;
use App\Models\Course;
use App\Models\Payment;
use App\Models\SupportRequest;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

class HelpCentre extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Help & rectification';

    protected string $view = 'filament.student.pages.help-centre';

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'category' => SupportRequestCategory::GeneralHelp->value,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tell us what you need')
                    ->description('Give enough detail for the support team to resolve your request quickly.')
                    ->schema([
                        Select::make('category')
                            ->options(SupportRequestCategory::options())
                            ->required(),
                        Select::make('course_id')
                            ->label('Related course (optional)')
                            ->options(fn (): array => Course::query()
                                ->whereHas('courseAccesses', fn ($query) => $query->where('user_id', auth()->id()))
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn (Course $course): array => [$course->id => "{$course->code} — {$course->name}"])
                                ->all())
                            ->searchable(),
                        Select::make('payment_id')
                            ->label('Payment reference (optional)')
                            ->options(fn (): array => Payment::query()
                                ->where('user_id', auth()->id())
                                ->latest()
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Payment $payment): array => [$payment->id => $payment->reference])
                                ->all())
                            ->searchable(),
                        TextInput::make('subject')
                            ->maxLength(180)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('message')
                            ->label('Describe the issue')
                            ->rows(6)
                            ->maxLength(5000)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submitRequest(): void
    {
        $data = $this->form->getState();

        validator($data, [
            'course_id' => [
                'nullable',
                Rule::exists('course_access', 'course_id')->where('user_id', auth()->id()),
            ],
            'payment_id' => [
                'nullable',
                Rule::exists('payments', 'id')->where('user_id', auth()->id()),
            ],
        ])->validate();

        SupportRequest::query()->create([
            ...$data,
            'user_id' => auth()->id(),
            'status' => SupportRequestStatus::Open,
        ]);

        $this->form->fill([
            'category' => SupportRequestCategory::GeneralHelp->value,
        ]);

        Notification::make()
            ->title('Support request sent')
            ->body('We have received your request and will review it shortly.')
            ->success()
            ->send();
    }

    /** @return Collection<int, SupportRequest> */
    #[Computed]
    public function requests(): Collection
    {
        return SupportRequest::query()
            ->where('user_id', auth()->id())
            ->with(['course:id,code,name', 'payment:id,reference'])
            ->latest()
            ->limit(5)
            ->get();
    }
}
