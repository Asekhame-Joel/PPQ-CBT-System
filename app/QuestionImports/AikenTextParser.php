<?php

namespace App\QuestionImports;

class AikenTextParser
{
    public function parse(string $contents): QuestionImportResult
    {
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', str_replace(["\r\n", "\r"], "\n", $contents)) ?? '';
        $blocks = preg_split('/\n\s*\n/', trim($contents), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $questions = [];
        $errors = [];

        foreach ($blocks as $index => $block) {
            $parsed = $this->parseBlock($block, $index + 1);

            if (is_string($parsed)) {
                $errors[] = $parsed;
            } else {
                $questions[] = $parsed;
            }
        }

        if ($blocks === []) {
            $errors[] = 'The text file does not contain any questions.';
        }

        return new QuestionImportResult($questions, $errors);
    }

    private function parseBlock(string $block, int $number): ParsedQuestion|string
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), fn (string $line): bool => $line !== ''));
        $questionText = array_shift($lines);

        if ($questionText === null) {
            return "Question {$number}: the question text is missing.";
        }

        $answerLabel = null;
        $rawOptions = [];

        foreach ($lines as $line) {
            if (preg_match('/^ANSWER\s*:\s*([A-Z])$/i', $line, $matches) === 1) {
                $answerLabel = strtoupper($matches[1]);

                continue;
            }

            if (preg_match('/^([A-Z])[.)]\s+(.+)$/i', $line, $matches) !== 1) {
                return "Question {$number}: '{$line}' is not a valid answer option or ANSWER line.";
            }

            $label = strtoupper($matches[1]);

            if (array_key_exists($label, $rawOptions)) {
                return "Question {$number}: option {$label} is duplicated.";
            }

            $rawOptions[$label] = trim($matches[2]);
        }

        if (count($rawOptions) < 2) {
            return "Question {$number}: at least two answer options are required.";
        }

        if ($answerLabel === null) {
            return "Question {$number}: the ANSWER line is missing.";
        }

        if (! array_key_exists($answerLabel, $rawOptions)) {
            return "Question {$number}: ANSWER {$answerLabel} does not match an option.";
        }

        $options = [];

        foreach ($rawOptions as $label => $optionText) {
            $options[] = new ParsedOption($label, $optionText, $label === $answerLabel);
        }

        return new ParsedQuestion($questionText, $options);
    }
}
