<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Local\Classes\TestDrives;

class TestDrivesComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    public function executeComponent()
    {
        $this->initResult();

        if (empty($this->arResult)) {
            ShowError('Данные не найдены');

            return;
        }

        $this->includeComponentTemplate();
    }

    private function initResult(): void
    {
        $this->arResult = ['TEST_DRIVES' => TestDrives::getList()];
    }
}
