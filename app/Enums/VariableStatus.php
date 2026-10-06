<?php

namespace App\Enums;

enum VariableStatus: string
{
    case Same = 'same';
    case Different = 'different';
    case Missing = 'missing';
    case Extra = 'extra';
}
