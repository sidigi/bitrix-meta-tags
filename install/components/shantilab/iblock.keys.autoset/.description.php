<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage('COMPONENT_NAME'),
	"DESCRIPTION" => GetMessage('COMPONENT_DESCRIPTION'),
	"ICON" => "/images/icon.gif",
	"SORT" => 100,
	"PATH" => array(
		"ID" => "shantilab",
		"NAME" => GetMessage('COMPONENT_MENU_NAME'),
		"CHILD" => array(
			"ID" => "metatags",
			"NAME" => GetMessage('COMPONENT_SUBMENU_NAME'),
		)
	),
);
?>