<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Settings;

use App\Entity\Person;
use App\Entity\Setting;
use App\Repository\SettingRepository;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class SettingManager implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;
    public function get(string $name, ?Person $user = null): ?string
    {
        // todo add a settings cache. load all global and user settings for the current user into the cache.
        $setting = $this->settingRepository()->findOneBy(['name' => $name, 'user' => $user]);
        if (!$setting) {
            return null;
        }

        return $setting->getValue();
    }

    public function set(string $name, ?string $value, ?Person $user = null): static
    {
        $setting = $this->settingRepository()->findOneBy(['name' => $name, 'user' => $user]);
        if ($value) {
            // set value, if given
            if (!$setting) {
                $setting = (new Setting())
                    ->setName($name)
                    ->setUser($user);
            }
            $setting->setValue($value);
            $this->settingRepository()->save($setting);
        } elseif ($setting) {
            // otherwise, remove the setting, if necessary
            $this->settingRepository()->remove($setting);
        }

        return $this;
    }

    public function displayNameFromName($name): string
    {
        return ucwords(str_replace('_', ' ', $name));
    }

    #[SubscribedService]
    private function settingRepository(): SettingRepository
    {
        return $this->container->get(__CLASS__ . '::' . __FUNCTION__);
    }
}