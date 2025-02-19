<?php

namespace App\Enum;

enum CategoryEnum: string
{
    case HUMANE = 'humane';
    case MEDICAL_CENTER = 'medical_center';
    case HOSPITAL = 'hospital';
    case DRUG_NOT_IN_BASKET = 'drug_not_in_basket';
    case VETERINARY = 'veterinary';
    case PHARMECIES = 'pharmecies';
}
