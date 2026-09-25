<?php

namespace App\Practice;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PracticeQuestionSelector
{
    /**
     * Selects from a cached list of active IDs, avoiding MySQL's expensive ORDER BY RAND().
     *
     * @return Collection<int, Question>
     */
    public function select(Course $course, int $questionCount, bool $randomize): Collection
    {
        $questionIds = Cache::remember(
            $this->cacheKey($course),
            now()->addHour(),
            fn (): array => Question::query()
                ->active()
                ->where('course_id', $course->id)
                ->orderBy('id')
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all(),
        );

        $selectedIds = collect($questionIds)
            ->when($randomize, fn (Collection $ids): Collection => $ids->shuffle())
            ->take($questionCount)
            ->values();

        $questionsById = Question::query()
            ->active()
            ->where('course_id', $course->id)
            ->whereIn('id', $selectedIds)
            ->with('options')
            ->get()
            ->keyBy('id');

        return $selectedIds
            ->map(fn (int $id): ?Question => $questionsById->get($id))
            ->filter()
            ->values();
    }

    public static function forget(Course|int $course): void
    {
        Cache::forget('practice-question-ids:'.($course instanceof Course ? $course->id : $course));
    }

    private function cacheKey(Course $course): string
    {
        return 'practice-question-ids:'.$course->id;
    }
}
