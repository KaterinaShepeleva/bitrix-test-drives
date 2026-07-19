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
        $this->id = $id ?? null;
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

    public function update($id = null)
    {
        echo static::FILTER_ALL;
        return 'update not implemented';
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

    private static function getStatusIdByCode($statusCode) {
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