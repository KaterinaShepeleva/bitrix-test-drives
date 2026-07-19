<?php

namespace Local\Classes;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;

Loader::includeModule('highloadblock');

class TestDrives {
    public static function getList()
    {
        return [
            'TEST_DRIVES' => [
                [
                    'CAR' => 'Audi A6',
                    'START_DATE' => '12.11.2026',
                    'END_DATE' => '26.11.2026',
                ],
                [
                    'CAR' => 'BMW X5',
                    'START_DATE' => '19.04.2026',
                    'END_DATE' => '19.05.2026',
                ],
                [
                    'CAR' => 'Tesla Model 3',
                    'START_DATE' => '03.10.2026',
                    'END_DATE' => '05.10.2026',
                ],
                [
                    'CAR' => 'Mercedes-Benz E-Class',
                    'START_DATE' => '14.02.2026',
                    'END_DATE' => '20.02.2026',
                ],
            ],
        ];
    }

    public static function create($data = [])
    {
        return 'create not implemented';
    }
}