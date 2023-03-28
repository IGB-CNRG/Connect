<?php
/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Settings;

use App\Repository\SettingRepository;

class SettingManager
{
    public function __construct(private readonly SettingRepository $repository){}

    public function get(string $name): string{
        $setting = $this->repository->findOneBy(['name'=>$name]);
        if(!$setting){
            return '';
        }
        return $setting->getValue();
    }

    public function set(string $name, string $value): bool {
        $setting = $this->repository->findOneBy(['name'=>$name]);
        if(!$setting){
            return false;
        }
        $setting->setValue($value);
        $this->repository->save($setting);
        return true;
    }
}