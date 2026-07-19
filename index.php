<?
/** @var object $APPLICATION; */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Сервис бронирования автомобилей");
?>

<div class="row">
	<div class="col-12 col-md-6">
		<? $APPLICATION->IncludeComponent(
			"testComponents:listing.cars",
			"",
			array()
		); ?>
	</div>
</div>