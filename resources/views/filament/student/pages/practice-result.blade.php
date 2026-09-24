<x-filament-panels::page>
    @php
        $result = $this->result;
    @endphp

    <x-filament::section>
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div>
                <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">
                    {{ $result->course->code }}
                </p>
                <h2 class="mt-1 text-xl font-bold text-gray-950 dark:text-white">
                    {{ $result->course->name }}
                </h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Submitted {{ $result->submitted_at->toDayDateTimeString() }}
                </p>
            </div>

            <div class="text-right">
                <p class="text-4xl font-bold text-primary-600 dark:text-primary-400">
                    {{ number_format((float) $result->score_percentage, 2) }}%
                </p>
                <x-filament::badge :color="$result->status === \App\Enums\AttemptStatus::Expired ? 'warning' : 'success'">
                    {{ $result->status === \App\Enums\AttemptStatus::Expired ? 'Time expired' : 'Submitted' }}
                </x-filament::badge>
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                <dt class="text-sm text-gray-500 dark:text-gray-400">Questions</dt>
                <dd class="mt-1 text-xl font-bold text-gray-950 dark:text-white">{{ $result->question_count }}</dd>
            </div>
            <div class="rounded-lg bg-success-50 p-4 dark:bg-success-400/10">
                <dt class="text-sm text-success-700 dark:text-success-400">Correct</dt>
                <dd class="mt-1 text-xl font-bold text-success-700 dark:text-success-400">{{ $result->correct_count }}</dd>
            </div>
            <div class="rounded-lg bg-danger-50 p-4 dark:bg-danger-400/10">
                <dt class="text-sm text-danger-700 dark:text-danger-400">Incorrect</dt>
                <dd class="mt-1 text-xl font-bold text-danger-700 dark:text-danger-400">{{ $result->incorrect_count }}</dd>
            </div>
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-white/5">
                <dt class="text-sm text-gray-500 dark:text-gray-400">Unanswered</dt>
                <dd class="mt-1 text-xl font-bold text-gray-950 dark:text-white">{{ $result->unanswered_count }}</dd>
            </div>
        </dl>
    </x-filament::section>

    <div class="space-y-6">
        @foreach ($result->questions as $question)
            @php
                $selectedOptionId = $question->answer?->selected_option_id;
            @endphp

            <x-filament::section>
                <div class="flex items-start justify-between gap-4">
                    <h3 class="font-semibold leading-7 text-gray-950 dark:text-white">
                        {{ $question->position }}. {{ $question->question_snapshot }}
                    </h3>

                    @if ($selectedOptionId === null)
                        <x-filament::badge color="gray">Unanswered</x-filament::badge>
                    @elseif ($question->answer->is_correct)
                        <x-filament::badge color="success">Correct</x-filament::badge>
                    @else
                        <x-filament::badge color="danger">Incorrect</x-filament::badge>
                    @endif
                </div>

                <div class="mt-5 space-y-2">
                    @foreach ($question->options_snapshot as $index => $option)
                        @php
                            $isCorrectOption = (int) $option['id'] === $question->correct_option_snapshot;
                            $isSelectedOption = (int) $option['id'] === $selectedOptionId;
                        @endphp

                        <div @class([
                            'flex items-start justify-between gap-3 rounded-lg border px-4 py-3 text-sm',
                            'border-success-500 bg-success-50 text-success-800 dark:bg-success-400/10 dark:text-success-300' => $isCorrectOption,
                            'border-danger-500 bg-danger-50 text-danger-800 dark:bg-danger-400/10 dark:text-danger-300' => $isSelectedOption && ! $isCorrectOption,
                            'border-gray-200 text-gray-700 dark:border-white/10 dark:text-gray-300' => ! $isCorrectOption && ! $isSelectedOption,
                        ])>
                            <span>
                                <span class="mr-1 font-semibold">{{ chr(65 + $index) }}.</span>
                                {{ $option['text'] }}
                            </span>

                            <span class="shrink-0 font-medium">
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
                    <div class="mt-5 border-t border-gray-200 pt-4 text-sm dark:border-white/10">
                        <p class="font-semibold text-gray-950 dark:text-white">Explanation</p>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">{{ $question->explanation_snapshot }}</p>
                    </div>
                @endif
            </x-filament::section>
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
</x-filament-panels::page>
