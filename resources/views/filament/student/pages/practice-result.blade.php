<x-filament-panels::page>
    <x-student-ui />

    @php
        $result = $this->result;
    @endphp

    <div class="ef-result-shell">
    <header class="ef-page-intro">
        <p class="ef-eyebrow">Practice complete</p>
        <h1 class="ef-title">Your result is ready.</h1>
        <p class="ef-subtitle">Review every answer and learn from the explanations below.</p>
    </header>

    <section class="ef-result-summary">
        <div class="ef-result-overview">
            <div class="ef-result-course">
                <span>{{ $result->course->code }}</span>
                <strong>{{ $result->course->name }}</strong>
                <small>Submitted {{ $result->submitted_at->toDayDateTimeString() }}</small>
            </div>

            <div class="ef-result-score-wrap">
                <div class="ef-result-score"><strong>{{ number_format((float) $result->score_percentage, 2) }}%</strong><span>Score</span></div>
                <span class="ef-result-status">
                    {{ $result->status === \App\Enums\AttemptStatus::Expired ? 'Time expired' : 'Submitted' }}
                </span>
            </div>
        </div>

        <dl class="ef-result-stats">
            <div>
                <dt>Questions</dt><dd>{{ $result->question_count }}</dd>
            </div>
            <div class="is-correct">
                <dt>Correct</dt><dd>{{ $result->correct_count }}</dd>
            </div>
            <div class="is-incorrect">
                <dt>Incorrect</dt><dd>{{ $result->incorrect_count }}</dd>
            </div>
            <div>
                <dt>Unanswered</dt><dd>{{ $result->unanswered_count }}</dd>
            </div>
        </dl>
    </section>

    <div class="ef-review-list">
        @foreach ($result->questions as $question)
            @php
                $selectedOptionId = $question->answer?->selected_option_id;
            @endphp

            <section class="ef-review-card">
                <div class="ef-review-heading">
                    <h3><span>{{ $question->position }}</span>{{ $question->question_snapshot }}</h3>

                    @if ($selectedOptionId === null)
                        <span class="ef-review-badge is-unanswered">Unanswered</span>
                    @elseif ($question->answer->is_correct)
                        <span class="ef-review-badge is-correct">Correct</span>
                    @else
                        <span class="ef-review-badge is-incorrect">Incorrect</span>
                    @endif
                </div>

                <div class="ef-review-options">
                    @foreach ($question->options_snapshot as $index => $option)
                        @php
                            $isCorrectOption = (int) $option['id'] === $question->correct_option_snapshot;
                            $isSelectedOption = (int) $option['id'] === $selectedOptionId;
                        @endphp

                        <div @class([
                            'ef-review-option',
                            'is-correct' => $isCorrectOption,
                            'is-incorrect' => $isSelectedOption && ! $isCorrectOption,
                        ])>
                            <span class="ef-review-letter">{{ chr(65 + $index) }}</span>
                            <span class="ef-review-option-text">{{ $option['text'] }}</span>

                            <span class="ef-review-note">
                                @if ($isCorrectOption && $isSelectedOption)
                                    Your answer · Correct
                                @elseif ($isCorrectOption)
                                    Correct answer
                                @elseif ($isSelectedOption)
                                    Your answer
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>

                @if (filled($question->explanation_snapshot))
                    <div class="ef-review-explanation">
                        <strong>Why this is correct</strong>
                        <p>{{ $question->explanation_snapshot }}</p>
                    </div>
                @endif

                <div class="mt-5 border-t border-gray-200 pt-4 dark:border-white/10">
                    @if ($reportingQuestionId === $question->id)
                        <div class="space-y-4">
                            <div>
                                <label for="report-reason-{{ $question->id }}" class="text-sm font-medium text-gray-950 dark:text-white">
                                    What is wrong with this question?
                                </label>
                                <select
                                    id="report-reason-{{ $question->id }}"
                                    wire:model="reportReason"
                                    class="mt-2 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                >
                                    @foreach (\App\Enums\QuestionReportReason::options() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('reportReason')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="report-comment-{{ $question->id }}" class="text-sm font-medium text-gray-950 dark:text-white">
                                    Explain the issue
                                </label>
                                <textarea
                                    id="report-comment-{{ $question->id }}"
                                    wire:model="reportComment"
                                    rows="3"
                                    maxlength="2000"
                                    class="mt-2 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                ></textarea>
                                @error('reportComment')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <x-filament::button wire:click="submitReport" size="sm" icon="heroicon-o-paper-airplane">
                                    Submit report
                                </x-filament::button>
                                <x-filament::button wire:click="cancelReport" size="sm" color="gray">
                                    Cancel
                                </x-filament::button>
                            </div>
                        </div>
                    @else
                        <x-filament::button
                            wire:click="beginReport({{ $question->id }})"
                            size="sm"
                            color="gray"
                            icon="heroicon-o-flag"
                        >
                            Report this question
                        </x-filament::button>
                    @endif
                </div>
            </section>
        @endforeach
    </div>

    <x-filament::button
        tag="a"
        href="{{ \App\Filament\Student\Pages\MyCourses::getUrl(panel: 'student') }}"
        color="gray"
        icon="heroicon-o-arrow-left"
    >
        Back to my courses
    </x-filament::button>
    </div>
</x-filament-panels::page>
