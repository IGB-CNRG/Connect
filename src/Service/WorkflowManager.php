<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Service;

use App\Entity\Person;

class WorkflowManager
{
    public function completeEntryStage(Person $person): void
    {
        $entryStage = $person->getEntryStage();
        // todo this needs to also fire off any associated events
        $person->setEntryStage($entryStage->next());
        // todo should logging be part of this function, or up to the controller?
    }
}