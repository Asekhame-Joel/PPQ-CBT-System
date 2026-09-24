<?php

namespace App\QuestionImports;

use Illuminate\Support\Str;

class QuestionFingerprint
{
    public static function make(string $questionText): string
    {
        return hash('sha256', Str::squish(Str::lower($questionText)));
    }
}
