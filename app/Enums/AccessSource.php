<?php

namespace App\Enums;

enum AccessSource: string
{
    case Payment = 'payment';
    case Admin = 'admin';
    case Promotion = 'promotion';
}
