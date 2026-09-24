<?php

namespace Tests\Unit\QuestionImports;

use App\QuestionImports\AikenTextParser;
use PHPUnit\Framework\TestCase;

class AikenTextParserTest extends TestCase
{
    public function test_it_parses_utf_8_aiken_questions(): void
    {
        $contents = "\xEF\xBB\xBFWhat is the capital of Nigeria?\r\nA. Lagos\r\nB. Abuja\r\nC. Kano\r\nANSWER: B\r\n\r\n2 + 2 equals?\r\nA) 3\r\nB) 4\r\nANSWER: B";

        $result = (new AikenTextParser)->parse($contents);

        $this->assertTrue($result->isValid());
        $this->assertCount(2, $result->questions);
        $this->assertSame('What is the capital of Nigeria?', $result->questions[0]->text);
        $this->assertSame(['A', 'B', 'C'], array_column($result->questions[0]->options, 'label'));
        $this->assertFalse($result->questions[0]->options[0]->isCorrect);
        $this->assertTrue($result->questions[0]->options[1]->isCorrect);
    }

    public function test_it_reports_invalid_blocks_without_discarding_valid_questions(): void
    {
        $contents = "Valid question?\nA. Yes\nB. No\nANSWER: A\n\nMissing answer?\nA. Yes\nB. No";

        $result = (new AikenTextParser)->parse($contents);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->questions);
        $this->assertSame(['Question 2: the ANSWER line is missing.'], $result->errors);
    }

    public function test_it_rejects_an_answer_that_does_not_match_an_option(): void
    {
        $result = (new AikenTextParser)->parse("Question?\nA. One\nB. Two\nANSWER: C");

        $this->assertSame(['Question 1: ANSWER C does not match an option.'], $result->errors);
    }
}
