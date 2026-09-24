<x-filament-panels::page>
    <x-filament::section
        heading="Download a template"
        description="Start with one of these examples and keep its question and answer structure."
        icon="heroicon-o-document-arrow-down"
    >
        <div class="flex flex-wrap gap-3">
            <x-filament::button wire:click="downloadTextTemplate" color="gray" icon="heroicon-o-document-text">
                Aiken TXT template
            </x-filament::button>

            <x-filament::button wire:click="downloadWordTemplate" color="gray" icon="heroicon-o-document">
                Word DOCX template
            </x-filament::button>
        </div>
    </x-filament::section>

    <form wire:submit="preview" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" icon="heroicon-o-eye">
            Preview questions
        </x-filament::button>
    </form>

    @if ($hasPreview)
        <div class="space-y-6">
            @if ($previewErrors !== [])
                <x-filament::section heading="File errors" icon="heroicon-o-exclamation-triangle">
                    <ul class="list-disc space-y-1 pl-5 text-sm text-danger-600 dark:text-danger-400">
                        @foreach ($previewErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-filament::section>
            @endif

            <x-filament::section
                heading="Question preview"
                description="{{ count($previewQuestions) }} valid question(s) found"
                icon="heroicon-o-document-magnifying-glass"
            >
                <div class="space-y-6">
                    @forelse ($previewQuestions as $index => $question)
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                            <p class="font-semibold text-gray-950 dark:text-white">
                                {{ $index + 1 }}. {{ $question['text'] }}
                            </p>

                            <ol class="mt-3 space-y-2">
                                @foreach ($question['options'] as $option)
                                    <li @class([
                                        'rounded-lg px-3 py-2 text-sm',
                                        'bg-success-50 font-medium text-success-700 dark:bg-success-400/10 dark:text-success-400' => $option['is_correct'],
                                        'bg-gray-50 text-gray-700 dark:bg-white/5 dark:text-gray-300' => ! $option['is_correct'],
                                    ])>
                                        {{ $option['label'] }}. {{ $option['text'] }}
                                        @if ($option['is_correct'])
                                            <span class="ml-2">Correct answer</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>

                            @if (filled($question['explanation']))
                                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Explanation:</span> {{ $question['explanation'] }}
                                </p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">No valid questions were found.</p>
                    @endforelse
                </div>
            </x-filament::section>

            @if ($previewErrors === [] && $previewQuestions !== [])
                <x-filament::button wire:click="import" wire:confirm="Import these questions into the selected course?" icon="heroicon-o-arrow-down-tray">
                    Confirm import
                </x-filament::button>
            @endif
        </div>
    @endif
</x-filament-panels::page>
