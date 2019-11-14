<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arTypesEx = CIBlockParameters::GetIBlockTypes(Array("-"=>" "));
$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];
$arModes = array(
	0 => GetMessage("COMPONENT_MODE_AUTO"),
	1 => GetMessage("COMPONENT_MODE_REQUEST"),
	2 => GetMessage("COMPONENT_MODE_COMPLEX"),
);
$arComponentParameters = array(
	"GROUPS" => array(
		"REQUEST_MODE" => array(
			"NAME" => GetMessage("COMPONENT_MODE_REQUEST"),
		),
		"COMPLEX_COMPONENT" => array(
			"NAME" => GetMessage("COMPONENT_MODE_COMPLEX"),
		)
	),
	"PARAMETERS" => array(
		"COMPONENT_MODE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("COMPONENT_MODE"),
			"TYPE" => "LIST",
			"VALUES" => $arModes,
		),
		"IBLOCK_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '={$_REQUEST["ID"]}',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>36000000),
		"ELEMENT_ID" => array(
			"PARENT" => "REQUEST_MODE",
			"NAME" => GetMessage("ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
		),
		"SECTION_ID" => array(
			"PARENT" => "REQUEST_MODE",
			"NAME" => GetMessage("SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
		),
		"ELEMENT_CODE" => array(
			"PARENT" => "REQUEST_MODE",
			"NAME" => GetMessage("ELEMENT_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_CODE"]}',
		),
		"SECTION_CODE" => array(
			"PARENT" => "REQUEST_MODE",
			"NAME" => GetMessage("SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_CODE"]}',
		),
		"COMPLEX_COMPONENT_PATH" => array(
			"PARENT" => "COMPLEX_COMPONENT",
			"NAME" => GetMessage("COMPLEX_COMPONENT_PATH"),
			"TYPE" => "STRING",
			"DEFAULT" => '/catalog/',
		),
		"COMPLEX_SECTION_PATH" => array(
			"PARENT" => "COMPLEX_COMPONENT",
			"NAME" => GetMessage("COMPLEX_SECTION_PATH"),
			"TYPE" => "STRING",
			"DEFAULT" => '#SECTION_ID#/',
		),
		"COMPLEX_ELEMENT_PATH" => array(
			"PARENT" => "COMPLEX_COMPONENT",
			"NAME" => GetMessage("COMPLEX_ELEMENT_PATH"),
			"TYPE" => "STRING",
			"DEFAULT" => '#SECTION_ID#/#ELEMENT_ID#/',
		),
	),
	
);
?>