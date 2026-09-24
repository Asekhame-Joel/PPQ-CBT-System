<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->courses as $course)
            @php
                $hasEnoughQuestions = $course->questions_count >= $course->min_question_count;
            @endphp

            <x-filament::section>
                <div class="flex h-full flex-col gap-5">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">
                                    {{ $course->code }}
                                </p>
                                <h2 class="mt-1 text-lg font-bold text-gray-950 dark:text-white">
                                    {{ $course->name }}
                                </h2>
                            </div>

                            <x-filament::badge :color="$course->is_unlocked ? 'success' : 'gray'">
                                {{ $course->is_unlocked ? 'Unlocked' : 'Locked' }}
                            </x-filament::badge>
                        </div>

                        @if (filled($course->description))
                            <p class="mt-3 line-clamp-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ $course->description }}
                            </p>
                        @endif
                    </div>

                    <dl class="grid grid-cols-3 gap-3 text-sm">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-gray-500 dark:text-gray-400">Level</dt>
                            <dd class="mt-1 font-semibold text-gray-950 dark:text-white">
                                {{ $course->level?->name ?? 'General' }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-gray-500 dark:text-gray-400">Questions</dt>
                            <dd class="mt-1 font-semibold text-gray-950 dark:text-white">
                                {{ number_format($course->questions_count) }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-gray-500 dark:text-gray-400">Price</dt>
                            <dd class="mt-1 font-semibold text-gray-950 dark:text-white">
                                &#8358;{{ number_format((float) $course->price, 2) }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-auto">
                        @if ($course->is_unlocked && $hasEnoughQuestions)
                            <x-filament::button
                                tag="a"
                                href="{{ \App\Filament\Student\Pages\PracticeSetup::getUrl(['course' => $course], panel: 'student') }}"
                                class="w-full"
                                icon="heroicon-o-play"
                            >
                                Start practice
                            </x-filament::button>
                        @elseif ($course->is_unlocked)
                            <x-filament::button disabled color="gray" class="w-full" icon="heroicon-o-clock">
                                Questions not available yet
                            </x-filament::button>
                        @elseif ($hasEnoughQuestions)
                            <x-filament::button
                                tag="a"
                                href="{{ \App\Filament\Student\Pages\Checkout::getUrl(['course' => $course], panel: 'student') }}"
                                class="w-full"
                                icon="heroicon-o-lock-closed"
                            >
                                Unlock course
                            </x-filament::button>
                        @else
                            <x-filament::button disabled color="gray" class="w-full" icon="heroicon-o-clock">
                                Not available yet
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                @if ($this->hasCompleteAcademicProfile())
                    <x-filament::section icon="heroicon-o-book-open" heading="No courses available">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            There are no active courses for your level and department yet.
                        </p>
                    </x-filament::section>
                @else
                    <x-filament::section icon="heroicon-o-user-circle" heading="Complete your academic profile">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Your department and level are required before we can show your available courses.
                        </p>
                    </x-filament::section>
                @endif
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
