<?php

namespace App\Enums;

enum AttemptType: string
{
    case NewPractice = 'new';
    case StartAgain = 'start_again';
    case NewQuestions = 'new_questions';
}
