<?php

use Bitrix\Main\Context;
use Local\Classes\TestDrives;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

$request = Context::getCurrent()->getRequest();
$data = $request->getPost('testDrive');

try {
    $result = TestDrives::create($data);

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