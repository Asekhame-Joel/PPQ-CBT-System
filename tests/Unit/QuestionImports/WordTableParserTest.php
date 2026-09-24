<?php

namespace Tests\Unit\QuestionImports;

use App\QuestionImports\WordTableParser;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class WordTableParserTest extends TestCase
{
    public function test_it_parses_structured_word_question_tables(): void
    {
        $path = $this->createDocx([
            [
                ['QUESTION', 'What is 2 + 2?'],
                ['A', 'Three'],
                ['B', 'Four'],
                ['ANSWER', 'B'],
                ['EXPLANATION', 'Two pairs make four.'],
            ],
        ]);

        try {
            $result = (new WordTableParser)->parse($path);

            $this->assertTrue($result->isValid());
            $this->assertCount(1, $result->questions);
            $this->assertSame('What is 2 + 2?', $result->questions[0]->text);
            $this->assertSame('Two pairs make four.', $result->questions[0]->explanation);
            $this->assertTrue($result->questions[0]->options[1]->isCorrect);
        } finally {
            unlink($path);
        }
    }

    public function test_it_reports_an_invalid_question_table(): void
    {
        $path = $this->createDocx([
            [
                ['QUESTION', 'Incomplete question?'],
                ['A', 'Only option'],
                ['ANSWER', 'A'],
            ],
        ]);

        try {
            $result = (new WordTableParser)->parse($path);

            $this->assertSame(['Question table 1: at least two answer options are required.'], $result->errors);
        } finally {
            unlink($path);
        }
    }

    public function test_it_rejects_a_file_that_is_not_a_docx_archive(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'word-test-');
        file_put_contents($path, 'not a zip');

        try {
            $result = (new WordTableParser)->parse($path);

            $this->assertSame(['The file is not a valid Word document.'], $result->errors);
        } finally {
            unlink($path);
        }
    }

    /** @param list<list<array{0: string, 1: string}>> $tables */
    private function createDocx(array $tables): string
    {
        $path = tempnam(sys_get_temp_dir(), 'word-test-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('word/document.xml', $this->documentXml($tables));
        $zip->close();

        return $path;
    }

    /** @param list<list<array{0: string, 1: string}>> $tables */
    private function documentXml(array $tables): string
    {
        $xml = '';

        foreach ($tables as $table) {
            $xml .= '<w:tbl>';

            foreach ($table as [$label, $value]) {
                $xml .= '<w:tr>'.$this->cellXml($label).$this->cellXml($value).'</w:tr>';
            }

            $xml .= '</w:tbl>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'.$xml.'</w:body></w:document>';
    }

    private function cellXml(string $value): string
    {
        return '<w:tc><w:p><w:r><w:t>'.htmlspecialchars($value, ENT_XML1).'</w:t></w:r></w:p></w:tc>';
    }
}
