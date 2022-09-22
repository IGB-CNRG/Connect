<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum;

enum DocumentCategory: string
{
    case Certificate = 'certificate';
    case Other = 'other';

    public function getChoiceLabel(): string
    {
        return match($this){
            DocumentCategory::Certificate => 'Certificate',
            DocumentCategory::Other => 'Other',
        };
    }
}