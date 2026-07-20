<?php

use Bitrix\Main\Context;
use Local\Classes\Cars;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

// получаем id автомобиля
$request = Context::getCurrent()->getRequest();
$cars = new Cars($request->getPost('id'));

$data = [
    'model' => $request->getPost('model'),
    'year' => $request->getPost('year'),
    'vin' => $request->getPost('vin'),
    'status' => $request->getPost('status'),
    'pricePerDay' => $request->getPost('pricePerDay'),
];

try {
    $result = $cars->update($data);

    echo json_encode([
        'success' => true,
        'data' => $result,
    ]);
} catch (\Throwable $e) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}