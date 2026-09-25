<?php

namespace App\QuestionImports;

class QuestionDuplicateFilterResult
{
    /**
     * @param  list<ParsedQuestion>  $questions
     * @param  list<string>  $skipped
     */
    public function __construct(
        public readonly array $questions,
        public readonly array $skipped,
    ) {}
}
