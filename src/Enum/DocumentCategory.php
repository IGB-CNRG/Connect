<?php

namespace App\Enum;

enum DocumentCategory: string
{
    case Certificate = 'certificate';
    case OfferLetter = 'offer_letter';
    case Other = 'other';

    public function getChoiceLabel(): string
    {
        return match($this){
            DocumentCategory::Certificate => 'Certificate',
            DocumentCategory::OfferLetter => 'Offer Letter',
            DocumentCategory::Other => 'Other',
        };
    }
}