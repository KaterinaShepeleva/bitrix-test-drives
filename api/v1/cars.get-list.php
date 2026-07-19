<?php

use Bitrix\Main\Context;
use Local\Classes\Cars;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

// получение кода статуса
$request = Context::getCurrent()->getRequest();
$status = $request->get('status');

try {
    $result = Cars::getList($status);

    echo json_encode([
        'success' => true,
        'data' => $result,
    ]);

} catch (\Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}