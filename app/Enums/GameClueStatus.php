<?php

namespace App\Enums;

enum GameClueStatus: string
{
    case Hidden = 'hidden';
    case Open = 'open';
    case Answered = 'answered';
}
