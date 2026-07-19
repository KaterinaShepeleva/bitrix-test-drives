<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arResult
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
                <td><?= $testDrive['DATE_START_FORMATTED'] ?></td>
                <td><?= $testDrive['DATE_END_FORMATTED'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>