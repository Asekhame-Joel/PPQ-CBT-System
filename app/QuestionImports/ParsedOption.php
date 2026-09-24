<?php

namespace App\QuestionImports;

class ParsedOption
{
    public function __construct(
        public readonly string $label,
        public readonly string $text,
        public readonly bool $isCorrect,
    ) {}
}
