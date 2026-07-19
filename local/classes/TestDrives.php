<?php

namespace Local\Classes;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Exception;

Loader::includeModule('highloadblock');

class TestDrives
{
    private const TESTDRIVES_HL_BLOCK_ID = 3;
    private const CARS_HL_BLOCK_ID = 1;
    private const STATUSES_HL_BLOCK_ID = 2;

    public static function getList()
    {
        $dataClass = static::getTestDrivesDataClass();

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
        // проверяем существование машины по id и её статус

        $carId = isset($data['carId']) ? (int)$data['carId'] : 0;
        if ($carId > 0) {
            $hlCars = HighloadBlockTable::getById(static::CARS_HL_BLOCK_ID)->fetch();
            $entityCars = HighloadBlockTable::compileEntity($hlCars);
            $dataClass = $entityCars->getDataClass();

            $hlStatuses = HighloadBlockTable::getById(static::STATUSES_HL_BLOCK_ID)->fetch();
            $entityStatuses = HighloadBlockTable::compileEntity($hlStatuses);

            $car = $dataClass::getList([
                'select' => [
                    'ID',
                    'PRICE_PER_DAY' => 'UF_PRICE_PER_DAY',
                    'STATUS' => 'STATUSES_REF.UF_CODE',
                ],
                'runtime' => [
                    new Reference(
                        'STATUSES_REF',
                        $entityStatuses,
                        Join::on('this.UF_STATUS', 'ref.ID')
                    )
                ],
                'filter' => ['=ID' => $carId],
            ])->fetch();

            if (!$car) {
                throw new ArgumentException('Автомобиль не найден');
            }

            if ($car['STATUS'] !== 'available') {
                throw new ArgumentException('Автомобиль недоступен для бронирования');
            }
        } else {
            throw new ArgumentException('Некорректный ID автомобиля');
        }

        // проверяем, что заданы корректные даты

        $dateStartStr = isset($data['dateStart'])
            ? trim((string)$data['dateStart'])
            : '';
        if ($dateStartStr === '') {
            throw new ArgumentException('Дата начала бронирования не задана');
        }

        try {
            $dateStart = new DateTime($dateStartStr);
        } catch (\Throwable $e) {
            throw new ArgumentException('Некорректная дата начала бронирования');
        }

        $dateEndStr = isset($data['dateEnd'])
            ? trim((string)$data['dateEnd'])
            : '';
        if ($dateEndStr === '') {
            throw new ArgumentException('Дата окончания бронирования не задана');
        }

        try {
            $dateEnd = new DateTime($dateEndStr);
        } catch (\Throwable $e) {
            throw new ArgumentException('Некорректная дата окончания бронирования');
        }

        if ($dateEnd < $dateStart) {
            throw new ArgumentException('Дата окончания должна быть позже даты начала');
        }

        // проверяем, что машина не забронирована
        $dataClass = static::getTestDrivesDataClass();
        $takenTestDrives = $dataClass::getList([
            'select' => ['ID'],
            'filter' => [
                '=UF_CAR' => $carId,
                '<UF_DATE_START' => $dateEnd,
                '>UF_DATE_END' => $dateStart,
            ],
            'limit' => 1,
        ])->fetch();

        if ($takenTestDrives) {
            throw new ArgumentException('Автомобиль уже забронирован');
        }

        // рассчитываем стоимость бронирования
        $days = $dateStart->getDiff($dateEnd)->days + 1; // дни интервала включительно
        $total = $car['PRICE_PER_DAY'] * $days;

        $result = $dataClass::add([
            'UF_CAR' => $carId,
            'UF_DATE_START' => $dateStart,
            'UF_DATE_END' => $dateEnd,
            'UF_TOTAL_COST' => $total,
        ]);

        if ($result->isSuccess()) {
            $id = $result->getId();
            return "Добавлено бронирование с ID = $id";
        } else {
            throw new Exception('Ошибка при добавлении нового бронирования в БД');
        }
    }

    private static function getTestDrivesDataClass()
    {
        $hlTestDrives = HighloadBlockTable::getById(static::TESTDRIVES_HL_BLOCK_ID)->fetch();
        $entityTestDrives = HighloadBlockTable::compileEntity($hlTestDrives);

        return $entityTestDrives->getDataClass();
    }
}