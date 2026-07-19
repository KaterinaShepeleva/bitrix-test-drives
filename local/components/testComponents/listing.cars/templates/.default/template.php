<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 */

?>

<table class="table">
    <thead>
        <tr>
            <th scope="col">Автомобиль</th>
            <th scope="col">Начало бронирования</th>
            <th scope="col">Окончание бронирования</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($arResult['TEST_DRIVES'] as $testDrive): ?>
            <tr>
                <td><?= $testDrive['CAR'] ?></td>
                <td><?= $testDrive['START_DATE'] ?></td>
                <td><?= $testDrive['END_DATE'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>