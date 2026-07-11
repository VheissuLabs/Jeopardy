<?php

namespace App\Enums;

enum BuzzStatus: string
{
    case Waiting = 'waiting';
    case Incorrect = 'incorrect';
}
