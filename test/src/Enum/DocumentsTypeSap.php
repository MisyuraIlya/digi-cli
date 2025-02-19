<?php

namespace App\Enum;

enum DocumentsTypeSap: string
{
    case ORDERS = 'Orders';
    case PRICE_OFFER = 'Quotations';
    case DELIVERY_ORDER = 'DeliveryNotes';
    case INVOICES = 'Invoices';
    case RETURN_ORDERS = 'Returns';
    
    case HISTORY = 'history';
    case DRAFT = 'draft';
    case APPROVE = 'approve';

}
