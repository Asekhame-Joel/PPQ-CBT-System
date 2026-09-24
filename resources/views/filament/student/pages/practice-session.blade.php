<x-filament-panels::page>
    <div
        x-data="{
            remaining: Math.max(0, {{ $expiresAtTimestamp }} - Math.floor(Date.now() / 1000)),
            timer: null,
            tick() {
                this.remaining = Math.max(0, {{ $expiresAtTimestamp }} - Math.floor(Date.now() / 1000));

                if (this.remaining === 0) {
                    clearInterval(this.timer);
                    $wire.expireAttempt();
                }
            },
            format() {
                const minutes = Math.floor(this.remaining / 60).toString().padStart(2, '0');
                const seconds = (this.remaining % 60).toString().padStart(2, '0');

                return `${minutes}:${seconds}`;
            },
        }"
        x-init="tick(); timer = setInterval(() => tick(), 1000)"
        class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_18rem]"
    >
        <div class="space-y-6">
            <x-filament::section>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Question {{ $currentPosition }} of {{ $questionCount }}
                        </p>
                        <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Answers are saved automatically.
                        </p>
                    </div>

                    <div
                        class="rounded-lg bg-gray-950 px-4 py-2 font-mono text-xl font-bold text-white"
                        :class="remaining <= 60 ? 'bg-danger-600' : 'bg-gray-950'"
                        aria-live="polite"
                    >
                        <span x-text="format()">--:--</span>
                    </div>
                </div>
            </x-filament::section>

            @php
                $question = $this->currentQuestion;
                $selectedOptionId = $question->answer?->selected_option_id;
            @endphp

            <x-filament::section>
                <fieldset x-bind:disabled="remaining === 0">
                    <legend class="text-lg font-semibold leading-7 text-gray-950 dark:text-white">
                        {{ $question->question_snapshot }}
                    </legend>

                    <div class="mt-6 space-y-3">
                        @foreach ($question->options_snapshot as $index => $option)
                            <label
                                wire:key="option-{{ $question->id }}-{{ $option['id'] }}"
                                @class([
                                    'flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition',
                                    'border-primary-500 bg-primary-50 dark:bg-primary-400/10' => (int) $selectedOptionId === (int) $option['id'],
                                    'border-gray-200 hover:border-primary-300 dark:border-white/10' => (int) $selectedOptionId !== (int) $option['id'],
                                ])
                            >
                                <input
                                    type="radio"
                                    name="attempt-question-{{ $question->id }}"
                                    value="{{ $option['id'] }}"
                                    @checked((int) $selectedOptionId === (int) $option['id'])
                                    wire:click="selectAnswer({{ $question->id }}, {{ (int) $option['id'] }})"
                                    class="mt-1 border-gray-300 text-primary-600 focus:ring-primary-600"
                                >
                                <span class="text-sm text-gray-800 dark:text-gray-200">
                                    <span class="mr-1 font-semibold">{{ chr(65 + $index) }}.</span>
                                    {{ $option['text'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </x-filament::section>

            <div class="flex items-center justify-between gap-3">
                <x-filament::button
                    wire:click="previousQuestion"
                    color="gray"
                    icon="heroicon-o-arrow-left"
                    :disabled="$currentPosition === 1"
                >
                    Previous
                </x-filament::button>

                <x-filament::button
                    wire:click="nextQuestion"
                    icon="heroicon-o-arrow-right"
                    icon-position="after"
                    :disabled="$currentPosition === $questionCount"
                >
                    Next
                </x-filament::button>
            </div>
        </div>

        <aside class="space-y-4">
            <x-filament::section heading="Questions" icon="heroicon-o-squares-2x2">
                <div class="grid grid-cols-5 gap-2">
                    @foreach ($this->questionStates as $questionState)
                        <button
                            type="button"
                            wire:click="goTo({{ $questionState->position }})"
                            @class([
                                'aspect-square rounded-lg text-sm font-semibold transition',
                                'ring-2 ring-primary-600 ring-offset-2 dark:ring-offset-gray-900' => $questionState->position === $currentPosition,
                                'bg-success-600 text-white' => filled($questionState->answer?->selected_option_id),
                                'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-200' => blank($questionState->answer?->selected_option_id),
                            ])
                            aria-label="Go to question {{ $questionState->position }}"
                        >
                            {{ $questionState->position }}
                        </button>
                    @endforeach
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="space-y-3">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Review the question palette before submitting. Unanswered questions receive no mark.
                    </p>

                    <x-filament::button
                        wire:click="submitAttempt"
                        wire:confirm="Submit this practice now? You will not be able to change your answers."
                        wire:loading.attr="disabled"
                        color="success"
                        icon="heroicon-o-check-circle"
                        class="w-full"
                    >
                        Submit practice
                    </x-filament::button>
                </div>
            </x-filament::section>
        </aside>
    </div>
</x-filament-panels::page>
