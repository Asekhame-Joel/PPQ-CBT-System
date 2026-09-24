<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->courseAccesses as $access)
            @php
                $course = $access->course;
                $canPractice = $course->questions_count >= $course->min_question_count;
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

                            @if ($course->level)
                                <x-filament::badge color="gray">
                                    {{ $course->level->name }}
                                </x-filament::badge>
                            @endif
                        </div>

                        @if (filled($course->description))
                            <p class="mt-3 line-clamp-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ $course->description }}
                            </p>
                        @endif
                    </div>

                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-gray-500 dark:text-gray-400">Questions</dt>
                            <dd class="mt-1 font-semibold text-gray-950 dark:text-white">
                                {{ number_format($course->questions_count) }}
                            </dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                            <dt class="text-gray-500 dark:text-gray-400">Access</dt>
                            <dd class="mt-1 font-semibold text-gray-950 dark:text-white">
                                {{ $access->expires_at ? 'Until '.$access->expires_at->toFormattedDateString() : 'No expiry' }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-auto">
                        @if ($canPractice)
                            <x-filament::button
                                tag="a"
                                href="{{ \App\Filament\Student\Pages\PracticeSetup::getUrl(['course' => $course], panel: 'student') }}"
                                class="w-full"
                                icon="heroicon-o-adjustments-horizontal"
                            >
                                Set up practice
                            </x-filament::button>
                        @else
                            <x-filament::button disabled color="gray" class="w-full" icon="heroicon-o-clock">
                                Questions not available yet
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <x-filament::section icon="heroicon-o-lock-closed" heading="No unlocked courses yet">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Courses you unlock will appear here when access is active.
                    </p>

                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ \App\Filament\Student\Pages\AvailableCourses::getUrl(panel: 'student') }}"
                            icon="heroicon-o-book-open"
                        >
                            Browse available courses
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
