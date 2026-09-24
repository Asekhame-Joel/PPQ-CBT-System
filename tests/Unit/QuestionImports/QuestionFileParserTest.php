<?php

namespace Tests\Unit\QuestionImports;

use App\QuestionImports\AikenTextParser;
use App\QuestionImports\QuestionFileParser;
use App\QuestionImports\WordTableParser;
use PHPUnit\Framework\TestCase;

class QuestionFileParserTest extends TestCase
{
    public function test_it_routes_a_text_file_to_the_aiken_parser(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'aiken-test-');
        file_put_contents($path, "Question?\nA. Yes\nB. No\nANSWER: A");

        try {
            $result = $this->parser()->parse($path, 'questions.TXT');

            $this->assertTrue($result->isValid());
            $this->assertCount(1, $result->questions);
        } finally {
            unlink($path);
        }
    }

    public function test_it_rejects_unsupported_file_extensions(): void
    {
        $result = $this->parser()->parse(__FILE__, 'questions.pdf');

        $this->assertSame(['Only .txt and .docx question files are supported.'], $result->errors);
    }

    public function test_it_rejects_non_utf_8_text(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'aiken-test-');
        file_put_contents($path, "Question?\nA. \xFF\nB. No\nANSWER: B");

        try {
            $result = $this->parser()->parse($path, 'questions.txt');

            $this->assertSame(['The text file must use UTF-8 encoding.'], $result->errors);
        } finally {
            unlink($path);
        }
    }

    private function parser(): QuestionFileParser
    {
        return new QuestionFileParser(new AikenTextParser, new WordTableParser);
    }
}
