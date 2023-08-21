<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig;

use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Entity\Theme;
use App\Service\HistoricityManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use Twig\Extension\RuntimeExtensionInterface;

class ConnectRuntime implements RuntimeExtensionInterface
{
    private HistoricityManager $historicityManager;

    public function __construct(HistoricityManager $historicityManager)
    {
        $this->historicityManager = $historicityManager;
    }

    public function getCurrent(Collection $collection): Collection
    {
        return $this->historicityManager->getCurrentEntities($collection);
    }

    public function getRoleName(string $rawRole): string
    {
        $roleNames = array_flip(Person::USER_ROLES);
        if(key_exists($rawRole, $roleNames)){
            return $roleNames[$rawRole];
        } else {
            return $rawRole;
        }
    }

    /**
     * Filters a collection of SupervisorAffiliations that match the given theme
     * @param Collection $collection
     * @param Theme $theme
     * @return ReadableCollection
     */
    public function filterByTheme(Collection $collection, Theme $theme): ReadableCollection
    {
        return $collection->filter(function(SupervisorAffiliation $affiliation) use ($theme) {
            return $affiliation->getSuperviseeThemeAffiliation()->getTheme() === $theme;
        });
    }
}