<?php

namespace App\QuestionImports;

class QuestionFileParser
{
    public function __construct(
        private readonly AikenTextParser $aikenTextParser,
        private readonly WordTableParser $wordTableParser,
    ) {}

    public function parse(string $path, ?string $originalName = null): QuestionImportResult
    {
        $extension = strtolower(pathinfo($originalName ?? $path, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt' => $this->parseTextFile($path),
            'docx' => $this->wordTableParser->parse($path),
            default => new QuestionImportResult(errors: ['Only .txt and .docx question files are supported.']),
        };
    }

    private function parseTextFile(string $path): QuestionImportResult
    {
        if (! is_file($path) || filesize($path) === false || filesize($path) > 2 * 1024 * 1024) {
            return new QuestionImportResult(errors: ['The text file is missing or exceeds the 2 MB limit.']);
        }

        $contents = file_get_contents($path);

        if ($contents === false || ! mb_check_encoding($contents, 'UTF-8')) {
            return new QuestionImportResult(errors: ['The text file must use UTF-8 encoding.']);
        }

        return $this->aikenTextParser->parse($contents);
    }
}
