<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Twig;

use App\Entity\HistoricalEntityInterface;
use App\Entity\Person;
use App\Entity\SupervisorAffiliation;
use App\Entity\Theme;
use App\Entity\ThemeAffiliation;
use App\Service\HistoricityManager;
use DateTimeInterface;
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

    public function getCurrent(Collection $collection): ReadableCollection
    {
        return $this->historicityManager->getCurrentEntities($collection);
    }

    public function getCurrentAndFuture(Collection $collection): ReadableCollection
    {
        return $this->historicityManager->getCurrentAndFutureEntities($collection);
    }

    public function getMember(Collection $collection): ReadableCollection
    {
        return $collection->filter(function (ThemeAffiliation $themeAffiliation) {
            return !$themeAffiliation->getTheme()->getIsOutsideGroup();
        });
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

    /**
     * @param Collection|HistoricalEntityInterface[] $entities
     * @return DateTimeInterface|null
     */
    public function earliest(Collection|array $entities): ?DateTimeInterface
    {
        if($entities instanceof Collection){
            $entities = $entities->toArray();
        }
        return $this->historicityManager->getEarliest($entities);
    }

    /**
     * @param Collection|HistoricalEntityInterface[] $entities
     * @return DateTimeInterface|null
     */
    public function latest(Collection|array $entities): ?DateTimeInterface
    {
        if($entities instanceof Collection){
            $entities = $entities->toArray();
        }
        return $this->historicityManager->getLatest($entities);
    }
}