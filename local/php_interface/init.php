<?php
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    null,
    [
        'Local\\Classes\\Cars' => '/local/classes/Cars.php',
        'Local\\Classes\\TestDrives' => '/local/classes/TestDrives.php',
    ]
);