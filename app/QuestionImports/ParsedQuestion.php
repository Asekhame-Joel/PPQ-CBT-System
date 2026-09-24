<?php

namespace App\QuestionImports;

class ParsedQuestion
{
    /** @param list<ParsedOption> $options */
    public function __construct(
        public readonly string $text,
        public readonly array $options,
        public readonly ?string $explanation = null,
    ) {}
}
