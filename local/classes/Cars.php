<?php

namespace Local\Classes;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Exception;

Loader::includeModule('highloadblock');

class Cars
{
    private const CARS_HL_BLOCK_ID = 1; // из админки
    private const STATUSES_HL_BLOCK_ID = 2;
    private const FILTER_ALL = ''; // пустой фильтр (выбирает все автомобили)
    private const STATUSES = ['available', 'repair'];

    private $id;

    public function __construct($id)
    {
        $carId = (int)$id;

        if ($carId <= 0) {
            throw new ArgumentException('Некорректный ID автомобиля');
        }

        if (static::checkCarById($carId)) {
            $this->id = $carId;
        } else {
            throw new ArgumentException('Автомобиль с таким ID не найден');
        }
    }

    public static function getList($status = '')
    {
        $statusCode = isset($status) ? (string)$status : static::FILTER_ALL;
        $dataClass = self::getCarsDataClass();

        // получаем сущность статусов для runtime
        $hlStatuses = HighloadBlockTable::getById(static::STATUSES_HL_BLOCK_ID)->fetch();
        $entityStatuses = HighloadBlockTable::compileEntity($hlStatuses);

        return $dataClass::getList([
            'select' => [
                'ID',
                'MODEL' => 'UF_MODEL',
                'YEAR' => 'UF_YEAR',
                'VIN' => 'UF_VIN',
                'STATUS' => 'STATUS_REF.UF_CODE',
                'PRICE_PER_DAY' => 'UF_PRICE_PER_DAY',
            ],
            'runtime' => [
                new Reference(
                    'STATUS_REF',
                    $entityStatuses,
                    Join::on('this.UF_STATUS', 'ref.ID')
                )
            ],
            'filter' => $statusCode !== static::FILTER_ALL ? ['=STATUS_REF.UF_CODE' => $statusCode] : [],
        ])->fetchAll();
    }

    public static function create($data = [])
    {
        $model = isset($data['model']) ? trim((string)$data['model']) : '';

        if ($model === '') {
            throw new ArgumentException('Поле "Модель" обязательное');
        }

        $dataClass = self::getCarsDataClass();
        $vin = isset($data['vin']) ? trim((string)$data['vin']) : '';

        // если VIN задан, проверяем, что он уникальный
        if ($vin !== '') {
            $checkVin = $dataClass::getList([
                'select' => ['ID'],
                'filter' => ['=UF_VIN' => $vin],
            ])->fetch();

            if ($checkVin) {
                throw new ArgumentException('Автомобиль с таким VIN уже существует');
            }
        }

        $status = $data['status'];
        if (!isset($status) || !in_array($status, static::STATUSES)) {
            $status = self::STATUSES[0]; // дефолтный статус - available
        }
        $statusId = static::getStatusIdByCode($status);

        $year = isset($data['year']) ? (int)$data['year'] : 0;
        $pricePerDay = isset($data['pricePerDay']) ? (int)$data['pricePerDay'] : 0;

        $result = $dataClass::add([
            'UF_MODEL' => $model,
            'UF_YEAR' => $year,
            'UF_VIN' => $vin,
            'UF_STATUS' => $statusId,
            'UF_PRICE_PER_DAY' => $pricePerDay,
        ]);

        if ($result->isSuccess()) {
            $id = $result->getId();
            return "Добавлен автомобиль $model с ID = $id";
        } else {
            throw new Exception('Ошибка при добавлении нового автомобиля в БД');
        }
    }

    public static function createMany($data = [])
    {
        return 'createMany not implemented';
    }

    public function update($data = [])
    {
        $fieldsToUpdate = [];
        $model = isset($data['model']) ? trim((string)$data['model']) : '';

        if ($model !== '') {
            $fieldsToUpdate['UF_MODEL'] = $model;
        }

        $dataClass = self::getCarsDataClass();
        $vin = isset($data['vin']) ? trim((string)$data['vin']) : '';

        // если VIN задан, проверяем, что он уникальный (исключая текущий автомобиль)
        if ($vin !== '') {
            $checkVin = $dataClass::getList([
                'select' => ['ID'],
                'filter' => [
                    '=UF_VIN' => $vin,
                    '!=ID' => $this->id,
                ],
            ])->fetch();

            if ($checkVin) {
                throw new ArgumentException('Автомобиль с таким VIN уже существует');
            } else {
                $fieldsToUpdate['UF_VIN'] = $vin;
            }
        }

        if (isset($data['status'])) {
            $status = (string)$data['status'];

            if (!in_array($status, static::STATUSES, true)) {
                throw new ArgumentException('Некорректный статус');
            }

            $fieldsToUpdate['UF_STATUS'] = static::getStatusIdByCode($status);
        }

        if (isset($data['year'])) {
            $fieldsToUpdate['UF_YEAR'] = (int)$data['year'];
        }

        if (isset($data['pricePerDay'])) {
            $fieldsToUpdate['UF_PRICE_PER_DAY'] = (int)$data['pricePerDay'];
        }

        if (empty($fieldsToUpdate)) {
            throw new ArgumentException('Не переданы данные для изменения');
        }

        $result = $dataClass::update($this->id, $fieldsToUpdate);

        if ($result->isSuccess()) {
            return "Изменен автомобиль с ID = $this->id";
        } else {
            throw new Exception('Ошибка при изменении автомобиля в БД');
        }
    }

    public function delete($id = null)
    {
        return 'delete not implemented';
    }

    private static function getCarsDataClass()
    {
        $hlCars = HighloadBlockTable::getById(static::CARS_HL_BLOCK_ID)->fetch();
        $entityCars = HighloadBlockTable::compileEntity($hlCars);

        return $entityCars->getDataClass();
    }

    private static function checkCarById($carId)
    {
        $dataClass = static::getCarsDataClass();

        $car = $dataClass::getList([
            'select' => ['ID'],
            'filter' => ['=ID' => $carId],
        ])->fetch();

        return (bool)$car;
    }

    private static function getStatusIdByCode($statusCode)
    {
        $hlStatuses = HighloadBlockTable::getById(static::STATUSES_HL_BLOCK_ID)->fetch();
        $entityStatuses = HighloadBlockTable::compileEntity($hlStatuses);
        $dataClass = $entityStatuses->getDataClass();

        $result = $dataClass::getList([
            'select' => ['ID'],
            'filter' => ['=UF_CODE' => $statusCode],
        ])->fetch();

        return $result ? (int)$result['ID'] : 0;
    }
}
