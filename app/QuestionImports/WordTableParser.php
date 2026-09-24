<?php

namespace App\QuestionImports;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use ZipArchive;

class WordTableParser
{
    private const MAX_FILE_BYTES = 10 * 1024 * 1024;

    private const MAX_DOCUMENT_XML_BYTES = 5 * 1024 * 1024;

    public function parse(string $path): QuestionImportResult
    {
        if (! is_file($path) || filesize($path) === false || filesize($path) > self::MAX_FILE_BYTES) {
            return new QuestionImportResult(errors: ['The Word file is missing or exceeds the 10 MB limit.']);
        }

        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return new QuestionImportResult(errors: ['The file is not a valid Word document.']);
        }

        try {
            $entry = $zip->statName('word/document.xml');

            if ($entry === false || ($entry['size'] ?? 0) > self::MAX_DOCUMENT_XML_BYTES) {
                return new QuestionImportResult(errors: ['The Word document content is missing or too large.']);
            }

            $xml = $zip->getFromName('word/document.xml');
        } finally {
            $zip->close();
        }

        if ($xml === false) {
            return new QuestionImportResult(errors: ['The Word document content could not be read.']);
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return new QuestionImportResult(errors: ['The Word document contains invalid XML.']);
        }

        return $this->parseDocument($document);
    }

    private function parseDocument(DOMDocument $document): QuestionImportResult
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $tables = $xpath->query('//w:tbl');
        $questions = [];
        $errors = [];

        if ($tables === false || $tables->length === 0) {
            return new QuestionImportResult(errors: ['The Word file does not contain any question tables.']);
        }

        foreach ($tables as $index => $table) {
            if (! $table instanceof DOMElement) {
                continue;
            }

            $parsed = $this->parseTable($xpath, $table, $index + 1);

            if (is_string($parsed)) {
                $errors[] = $parsed;
            } else {
                $questions[] = $parsed;
            }
        }

        return new QuestionImportResult($questions, $errors);
    }

    private function parseTable(DOMXPath $xpath, DOMElement $table, int $number): ParsedQuestion|string
    {
        $rows = $xpath->query('./w:tr', $table);
        $fields = [];

        foreach ($rows ?: [] as $row) {
            $cells = $xpath->query('./w:tc', $row);

            if ($cells === false || $cells->length < 2) {
                return "Question table {$number}: every row must have a field and a value.";
            }

            $label = strtoupper(trim($this->cellText($xpath, $cells->item(0))));
            $value = trim($this->cellText($xpath, $cells->item(1)));

            if ($label === '' || $value === '') {
                return "Question table {$number}: field names and values cannot be empty.";
            }

            if (array_key_exists($label, $fields)) {
                return "Question table {$number}: field {$label} is duplicated.";
            }

            $fields[$label] = $value;
        }

        $text = $fields['QUESTION'] ?? null;
        $answerLabel = isset($fields['ANSWER']) ? strtoupper($fields['ANSWER']) : null;
        $explanation = $fields['EXPLANATION'] ?? null;
        $rawOptions = array_filter(
            $fields,
            fn (string $value, string $label): bool => preg_match('/^[A-Z]$/', $label) === 1,
            ARRAY_FILTER_USE_BOTH,
        );

        if ($text === null) {
            return "Question table {$number}: the QUESTION row is missing.";
        }

        if (count($rawOptions) < 2) {
            return "Question table {$number}: at least two answer options are required.";
        }

        if ($answerLabel === null) {
            return "Question table {$number}: the ANSWER row is missing.";
        }

        if (! array_key_exists($answerLabel, $rawOptions)) {
            return "Question table {$number}: ANSWER {$answerLabel} does not match an option.";
        }

        $options = [];

        foreach ($rawOptions as $label => $optionText) {
            $options[] = new ParsedOption($label, $optionText, $label === $answerLabel);
        }

        return new ParsedQuestion($text, $options, $explanation);
    }

    private function cellText(DOMXPath $xpath, ?DOMNode $cell): string
    {
        if ($cell === null) {
            return '';
        }

        $paragraphs = $xpath->query('.//w:p', $cell);
        $parts = [];

        foreach ($paragraphs ?: [] as $paragraph) {
            $texts = $xpath->query('.//w:t', $paragraph);
            $part = '';

            foreach ($texts ?: [] as $text) {
                $part .= $text->textContent;
            }

            if ($part !== '') {
                $parts[] = $part;
            }
        }

        return implode("\n", $parts);
    }
}
