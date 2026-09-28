<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class SiteZoneSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            ['name' => 'ХГРП', 'code' => 'hgrp'],
            ['name' => 'БГРП', 'code' => 'bgrp'],
            ['name' => 'ВБК', 'code' => 'vbk'],
        ];

        foreach ($sites as $site) {
            Site::updateOrCreate(['code' => $site['code']], $site);
        }

        $zones = [
            ['name' => 'Сбор бытовых отходов', 'code' => 'waste_household'],
            ['name' => 'Сбор отходов по классам', 'code' => 'waste_classes'],
            ['name' => 'Хранение масел и ГСМ', 'code' => 'oil_fuel'],
            ['name' => 'Маркировка и таблички', 'code' => 'labels'],
        ];

        foreach ($zones as $zone) {
            Zone::updateOrCreate(['code' => $zone['code']], $zone);
        }
    }
}