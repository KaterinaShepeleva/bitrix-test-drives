<?php

namespace Local\Classes;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

Loader::includeModule('highloadblock');

class TestDrives {
    private const TESTDRIVES_HL_BLOCK_ID = 3;
    private const CARS_HL_BLOCK_ID = 1;

    public static function getList()
    {
        $hlTestDrives = HighloadBlockTable::getById(static::TESTDRIVES_HL_BLOCK_ID)->fetch();
        $entityTestDrives = HighloadBlockTable::compileEntity($hlTestDrives);
        $dataClass = $entityTestDrives->getDataClass();

        $hlCars = HighloadBlockTable::getById(static::CARS_HL_BLOCK_ID)->fetch();
        $entityCars = HighloadBlockTable::compileEntity($hlCars);

        return $dataClass::getList([
            'select' => [
                'ID',
                'CAR' => 'CARS_REF.UF_MODEL',
                'DATE_START' => 'UF_DATE_START',
                'DATE_END' => 'UF_DATE_END',
                'TOTAL_COST' => 'UF_TOTAL_COST',
            ],
            'runtime' => [
                new Reference(
                    'CARS_REF',
                    $entityCars,
                    Join::on('this.UF_CAR', 'ref.ID')
                )
            ],
        ])->fetchAll();
    }

    public static function create($data = [])
    {
        return 'create not implemented';
    }
}