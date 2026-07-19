<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// use Local\Classes\Cars;
use Local\Classes\TestDrives;

class TestDrivesComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        // $arParams['USER_ID'] ??= 0;
        // $arParams['SHOW_EMAIL'] ??= 'Y';

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
        /*
        $userId = (int)$this->arParams['USER_ID'];
        if ($userId < 1) {
            return;
        }

        $user = \Bitrix\Main\UserTable::query()
            ->setSelect([
                'NAME',
                'EMAIL',
                'PERSONAL_PHOTO',
            ])
            ->where('ID', $userId)
            ->fetch();
        if (empty($user)) {
            return;
        }

        $this->arResult = [
            'NAME' => $user['NAME'],
            'EMAIL' => $user['EMAIL'],
        ];
        */

        $this->arResult = TestDrives::getList();
    }
}
