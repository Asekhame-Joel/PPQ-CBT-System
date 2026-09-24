<?php

namespace App\QuestionImports;

class QuestionImportResult
{
    /**
     * @param  list<ParsedQuestion>  $questions
     * @param  list<string>  $errors
     */
    public function __construct(
        public readonly array $questions = [],
        public readonly array $errors = [],
    ) {}

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
