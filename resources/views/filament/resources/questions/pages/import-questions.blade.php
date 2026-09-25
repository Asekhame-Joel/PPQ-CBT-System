<x-filament-panels::page>
    <x-admin-ui />

    <header class="ea-intro">
        <p class="ea-eyebrow">Question bank</p>
        <h2 class="ea-title">Upload, check, then publish.</h2>
        <p class="ea-copy">Use the Moodle-style TXT template or the structured Word template. Nothing is added until the preview has no errors and you confirm the import.</p>
    </header>

    <div class="ea-steps">
        <div class="ea-step"><span class="ea-step-number">1</span><div><strong>Choose the course</strong><span>Select where these questions belong.</span></div></div>
        <div class="ea-step"><span class="ea-step-number">2</span><div><strong>Upload DOCX or TXT</strong><span>The file is validated before import.</span></div></div>
        <div class="ea-step"><span class="ea-step-number">3</span><div><strong>Review and confirm</strong><span>Check answers and fix any reported errors.</span></div></div>
    </div>

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
            @if ($skippedDuplicates !== [])
                <x-filament::section
                    heading="{{ count($skippedDuplicates) }} duplicate question(s) skipped"
                    description="Duplicates are not included in the preview and will not be imported."
                    icon="heroicon-o-document-minus"
                >
                    <ul class="list-disc space-y-1 pl-5 text-sm text-gray-600">
                        @foreach ($skippedDuplicates as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </x-filament::section>
            @endif

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
                description="{{ count($previewQuestions) }} question(s) in this preview. Edit or delete them before importing."
                icon="heroicon-o-document-magnifying-glass"
            >
                <div class="space-y-6">
                    @forelse ($previewQuestions as $index => $question)
                        <div wire:key="preview-question-{{ $index }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <p class="font-semibold text-gray-950">Question {{ $index + 1 }}</p>
                                <x-filament::button
                                    type="button"
                                    color="danger"
                                    size="sm"
                                    outlined
                                    icon="heroicon-o-trash"
                                    wire:click="removePreviewQuestion({{ $index }})"
                                    wire:confirm="Delete question {{ $index + 1 }} from this import?"
                                >
                                    Delete
                                </x-filament::button>
                            </div>

                            <label class="block text-sm font-medium text-gray-700" for="question-{{ $index }}">Question text</label>
                            <textarea
                                id="question-{{ $index }}"
                                rows="2"
                                wire:model.blur="previewQuestions.{{ $index }}.text"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            ></textarea>

                            <ol class="mt-3 space-y-2">
                                @foreach ($question['options'] as $optionIndex => $option)
                                    <li class="flex items-center gap-3 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                        <input
                                            type="radio"
                                            name="correct-answer-{{ $index }}"
                                            value="{{ $option['label'] }}"
                                            wire:model="previewQuestions.{{ $index }}.correct_label"
                                            aria-label="Mark option {{ $option['label'] }} as correct"
                                            class="border-gray-300 text-primary-600 focus:ring-primary-500"
                                        >
                                        <span class="w-5 shrink-0 font-semibold">{{ $option['label'] }}.</span>
                                        <input
                                            type="text"
                                            wire:model.blur="previewQuestions.{{ $index }}.options.{{ $optionIndex }}.text"
                                            class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            aria-label="Option {{ $option['label'] }} text"
                                        >
                                    </li>
                                @endforeach
                            </ol>

                            <label class="mt-4 block text-sm font-medium text-gray-700" for="explanation-{{ $index }}">Explanation <span class="font-normal text-gray-500">(optional)</span></label>
                            <textarea
                                id="explanation-{{ $index }}"
                                rows="2"
                                wire:model.blur="previewQuestions.{{ $index }}.explanation"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            ></textarea>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">No valid questions were found.</p>
                    @endforelse
                </div>
            </x-filament::section>

            <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <x-filament::button type="button" wire:click="validatePreview" color="gray" icon="heroicon-o-check-circle">
                    Check changes
                </x-filament::button>

                @if ($previewErrors === [] && $previewQuestions !== [])
                    <x-filament::button wire:click="import" wire:confirm="Import these {{ count($previewQuestions) }} questions into the selected course?" icon="heroicon-o-arrow-down-tray">
                        Confirm import
                    </x-filament::button>
                @else
                    <p class="text-sm text-gray-600">Fix or delete the items listed above, then select <strong>Check changes</strong>.</p>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
