<?php

namespace App\Enum;

enum HistoryDocumentTypeEnum: string
{
    case ORDER = 'ORDER';
    case QUOATE = 'QUOATE';
    case RETURN = 'RETURN';
}
