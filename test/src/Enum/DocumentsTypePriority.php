<?php

namespace App\Enum;
enum DocumentsTypePriority: string
{
    case ORDERS = 'orders';
    case PRICE_OFFER = 'priceOffer';
    case DELIVERY_ORDER = 'deliveryOrder';
    case AI_INVOICE = 'aiInvoice';
    case CI_INVOICE = 'ciInvoice';
    case RETURN_ORDERS = 'returnOrder';
    case HISTORY = 'history';
    case DRAFT = 'draft';
    case APPROVE = 'approve';
}
