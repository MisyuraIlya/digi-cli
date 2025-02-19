<?php

namespace App\Enum;

enum AgentObjectiveType: string
{
    case TASK = 'task';
    case VISIT = 'visit';
    case MIX = 'mix';
}
