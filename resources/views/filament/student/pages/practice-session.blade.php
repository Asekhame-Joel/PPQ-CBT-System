<x-filament-panels::page>
    <x-student-ui />

    @php
        $questions = $this->questions;
        $answers = $questions->mapWithKeys(fn ($question) => [$question->id => $question->answer?->selected_option_id]);
    @endphp

    <div
        x-data="{
            currentPosition: {{ $currentPosition }},
            questionCount: {{ $questionCount }},
            answers: @js($answers),
            showSubmitConfirmation: false,
            remaining: Math.max(0, {{ $expiresAtTimestamp }} - Math.floor(Date.now() / 1000)),
            timer: null,
            goTo(position) {
                if (position >= 1 && position <= this.questionCount) {
                    this.currentPosition = position;
                    window.scrollTo({ top: 0, behavior: 'auto' });
                }
            },
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
        <div class="ef-exam-main" wire:ignore>
            <section class="ef-exam-status">
                <div class="ef-exam-status-copy">
                    <span class="ef-exam-kicker">Question <span x-text="currentPosition"></span> of {{ $questionCount }}</span>
                    <strong>{{ $courseCode }} practice exam</strong>
                    <span>Choose one answer. Your selection is saved automatically.</span>
                </div>

                <div class="ef-exam-clock" :class="remaining <= 60 ? 'is-urgent' : ''" aria-live="polite">
                    <span>Time remaining</span>
                    <strong x-text="format()">--:--</strong>
                </div>

                <div class="ef-progress-track" aria-hidden="true">
                    <span :style="`width: ${(currentPosition / questionCount) * 100}%`"></span>
                </div>
            </section>

            @foreach ($questions as $question)
                <section x-show="currentPosition === {{ $question->position }}" x-cloak class="ef-question-card">
                    <fieldset x-bind:disabled="remaining === 0">
                        <legend><span class="ef-question-number">{{ $question->position }}</span><span>{{ $question->question_snapshot }}</span></legend>

                        <div class="ef-answer-list">
                            @foreach ($question->options_snapshot as $index => $option)
                                <label
                                    class="ef-answer-option"
                                    :class="{ 'is-selected': Number(answers[{{ $question->id }}]) === {{ (int) $option['id'] }} }"
                                >
                                    <input
                                        type="radio"
                                        name="attempt-question-{{ $question->id }}"
                                        value="{{ $option['id'] }}"
                                        :checked="Number(answers[{{ $question->id }}]) === {{ (int) $option['id'] }}"
                                        @click="answers[{{ $question->id }}] = {{ (int) $option['id'] }}; $wire.selectAnswer({{ $question->id }}, {{ (int) $option['id'] }})"
                                    >
                                    <span class="ef-answer-letter">{{ chr(65 + $index) }}</span>
                                    <span class="ef-answer-text">{{ $option['text'] }}</span>
                                    <span class="ef-answer-check">✓</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                </section>
            @endforeach

            <div class="ef-exam-navigation">
                <x-filament::button
                    x-on:click="goTo(currentPosition - 1)"
                    color="gray"
                    icon="heroicon-o-arrow-left"
                    x-bind:disabled="currentPosition === 1"
                >
                    Previous
                </x-filament::button>

                <x-filament::button
                    x-on:click="currentPosition === questionCount ? showSubmitConfirmation = true : goTo(currentPosition + 1)"
                    icon="heroicon-o-arrow-right"
                    icon-position="after"
                    x-bind:disabled="remaining === 0"
                >
                    <span x-show="currentPosition < questionCount">Next</span>
                    <span x-cloak x-show="currentPosition === questionCount">Submit practice</span>
                </x-filament::button>
            </div>
        </div>

        <aside class="ef-exam-sidebar">
            <section class="ef-palette-card">
                <div class="ef-palette-heading">
                    <div><strong>Questions</strong><span>Jump to any question</span></div>
                    <span x-text="Object.values(answers).filter(Boolean).length + '/{{ $questionCount }}'"></span>
                </div>
                <div class="ef-question-palette">
                    @foreach ($questions as $question)
                        <button
                            type="button"
                            x-on:click="goTo({{ $question->position }})"
                            class="ef-palette-number"
                            :class="{
                                'is-current': currentPosition === {{ $question->position }},
                                'is-answered': Boolean(answers[{{ $question->id }}]),
                            }"
                            aria-label="Go to question {{ $question->position }}"
                        >
                            {{ $question->position }}
                        </button>
                    @endforeach
                </div>
                <div class="ef-palette-legend"><span><i class="answered"></i>Answered</span><span><i class="current"></i>Current</span></div>
            </section>

            <section class="ef-submit-card">
                    <strong>Ready to finish?</strong>
                    <p>Review unanswered questions before submitting. You cannot change answers afterwards.</p>

                    <x-filament::button
                        x-on:click="showSubmitConfirmation = true"
                        color="success"
                        icon="heroicon-o-check-circle"
                        class="w-full"
                    >
                        Submit practice
                    </x-filament::button>
            </section>
        </aside>

        <div
            x-cloak
            x-show="showSubmitConfirmation"
            x-on:keydown.escape.window="showSubmitConfirmation = false"
            class="ef-submit-dialog-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="submit-practice-title"
        >
            <div x-show="showSubmitConfirmation" x-transition class="ef-submit-dialog">
                <span class="ef-submit-dialog-icon">✓</span>
                <p class="ef-eyebrow">Final check</p>
                <h2 id="submit-practice-title">Submit your practice?</h2>
                <p>You have answered <strong x-text="Object.values(answers).filter(Boolean).length"></strong> of {{ $questionCount }} questions. Submitted answers cannot be changed.</p>
                <div class="ef-submit-dialog-actions">
                    <x-filament::button x-on:click="showSubmitConfirmation = false" color="gray">Keep reviewing</x-filament::button>
                    <x-filament::button x-on:click="showSubmitConfirmation = false; $wire.submitAttempt()" color="success" icon="heroicon-o-check-circle">Yes, submit</x-filament::button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
