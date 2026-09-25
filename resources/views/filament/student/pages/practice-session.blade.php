<x-filament-panels::page>
    <x-student-ui />

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
        class="ef-exam-shell"
    >
        <div class="ef-exam-main">
            <section class="ef-exam-status">
                <div class="ef-exam-status-copy">
                    <span class="ef-exam-kicker">Question {{ $currentPosition }} of {{ $questionCount }}</span>
                    <strong>{{ $courseCode }} practice exam</strong>
                    <span>Choose one answer. Your selection is saved automatically.</span>
                </div>

                <div class="ef-exam-clock" :class="remaining <= 60 ? 'is-urgent' : ''" aria-live="polite">
                    <span>Time remaining</span>
                    <strong x-text="format()">--:--</strong>
                </div>

                <div class="ef-progress-track" aria-hidden="true">
                    <span style="width: {{ ($currentPosition / max($questionCount, 1)) * 100 }}%"></span>
                </div>
            </section>

            @php
                $question = $this->currentQuestion;
                $selectedOptionId = $question->answer?->selected_option_id;
            @endphp

            <section class="ef-question-card">
                <fieldset x-bind:disabled="remaining === 0">
                    <legend><span class="ef-question-number">{{ $currentPosition }}</span><span>{{ $question->question_snapshot }}</span></legend>

                    <div class="ef-answer-list">
                        @foreach ($question->options_snapshot as $index => $option)
                            <label
                                wire:key="option-{{ $question->id }}-{{ $option['id'] }}"
                                @class([
                                    'ef-answer-option',
                                    'is-selected' => (int) $selectedOptionId === (int) $option['id'],
                                ])
                            >
                                <input
                                    type="radio"
                                    name="attempt-question-{{ $question->id }}"
                                    value="{{ $option['id'] }}"
                                    @checked((int) $selectedOptionId === (int) $option['id'])
                                    wire:click="selectAnswer({{ $question->id }}, {{ (int) $option['id'] }})"
                                >
                                <span class="ef-answer-letter">{{ chr(65 + $index) }}</span>
                                <span class="ef-answer-text">{{ $option['text'] }}</span>
                                <span class="ef-answer-check">✓</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </section>

            <div class="ef-exam-navigation">
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

        <aside class="ef-exam-sidebar">
            <section class="ef-palette-card">
                <div class="ef-palette-heading">
                    <div><strong>Questions</strong><span>Jump to any question</span></div>
                    <span>{{ $this->questionStates->filter(fn ($state) => filled($state->answer?->selected_option_id))->count() }}/{{ $questionCount }}</span>
                </div>
                <div class="ef-question-palette">
                    @foreach ($this->questionStates as $questionState)
                        <button
                            type="button"
                            wire:click="goTo({{ $questionState->position }})"
                            @class([
                                'ef-palette-number',
                                'is-current' => $questionState->position === $currentPosition,
                                'is-answered' => filled($questionState->answer?->selected_option_id),
                            ])
                            aria-label="Go to question {{ $questionState->position }}"
                        >
                            {{ $questionState->position }}
                        </button>
                    @endforeach
                </div>
                <div class="ef-palette-legend"><span><i class="answered"></i>Answered</span><span><i class="current"></i>Current</span></div>
            </section>

            <section class="ef-submit-card">
                    <strong>Ready to finish?</strong>
                    <p>Review unanswered questions before submitting. You cannot change answers afterwards.</p>

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
            </section>
        </aside>
    </div>
</x-filament-panels::page>
