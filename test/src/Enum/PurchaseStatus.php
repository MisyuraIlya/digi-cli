<?php

namespace App\Enum;

enum PurchaseStatus: string
{
    case PAID = 'paid';
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case FAILED = 'failed';
    case WAITING_APPROVE = 'waiting_approve';
}