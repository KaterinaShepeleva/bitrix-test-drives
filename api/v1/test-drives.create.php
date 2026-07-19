<?php

use Bitrix\Main\Context;
use Local\Classes\TestDrives;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

// получаем информацию о тест-драйве
$request = Context::getCurrent()->getRequest();
$data = [
    'carId' => $request->getPost('carId'),
    'dateStart' => $request->getPost('dateStart'),
    'dateEnd' => $request->getPost('dateEnd'),
];

try {
    $result = TestDrives::create($data);

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