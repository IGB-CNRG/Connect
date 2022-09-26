<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Enum;

enum Workflow: string
{
    case PersonEntry = 'person_entry';
    case PersonExit= 'person_exit';

    public function stageType(): string
    {
        return match ($this){
            self::PersonEntry => PersonEntryStage::class,
            self::PersonExit => throw new \Exception('To be implemented'),
        };
    }
}
