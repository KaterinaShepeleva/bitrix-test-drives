<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arResult
 */

if (!empty($arResult['TEST_DRIVES'])) {
    foreach ($arResult['TEST_DRIVES'] as &$item) {
        $item['DATE_START_FORMATTED'] = $item['DATE_START']->format('d.m.Y H:i');
        $item['DATE_END_FORMATTED'] = $item['DATE_END']->format('d.m.Y H:i');
    }
}