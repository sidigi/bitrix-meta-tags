<?php
namespace Shantilab\MetaTags\Key;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class KeysTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'shantilab_metatags_keys';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('KEYS_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('KEYS_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'MODIFIED_BY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('KEYS_ENTITY_MODIFIED_BY_FIELD'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('KEYS_ENTITY_DATE_CREATE_FIELD'),
			),
			'CREATED_BY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('KEYS_ENTITY_CREATED_BY_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('KEYS_ENTITY_ACTIVE_FIELD'),
			),
			'ACTIVE_FROM' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('KEYS_ENTITY_ACTIVE_FROM_FIELD'),
			),
			'ACTIVE_TO' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('KEYS_ENTITY_ACTIVE_TO_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('KEYS_ENTITY_SORT_FIELD'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage('KEYS_ENTITY_CODE_FIELD'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('KEYS_ENTITY_NAME_FIELD'),
			),
			'VALUE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateValue'),
				'title' => Loc::getMessage('KEYS_ENTITY_VALUE_FIELD'),
			),
			'C_SITE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCSite'),
				'title' => Loc::getMessage('KEYS_ENTITY_C_SITE_FIELD'),
			),
			'C_TEMPLATE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCTemplate'),
				'title' => Loc::getMessage('KEYS_ENTITY_C_TEMPLATE_FIELD'),
			),
			'C_REQUEST' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCRequest'),
				'title' => Loc::getMessage('KEYS_ENTITY_C_REQUEST_FIELD'),
			),
			'C_PHP' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCPhp'),
				'title' => Loc::getMessage('KEYS_ENTITY_C_PHP_FIELD'),
			),
			"KEY" => array(
				'data_type' => 'ShantiLab\MetaTags\Key\PagesTable',
				'reference' => array(
					'=this.ID' => 'ref.KEY_ID'
				),
			),
		);
	}

	public static function validateCode()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateName()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateValue()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateCSite()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateCTemplate()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateCRequest()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateCPhp()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	public function checkFieldsDB(&$arFields,$id = null){
		global $DB,$USER;

		$error = array();

		$userId = $USER->getId();

		if (isset($arFields["CODE"]))
			$arFields["CODE"] = strtoupper(str_replace(' ','',$arFields["CODE"]));
		if (isset($arFields["NAME"]))
			$arFields["NAME"] = trim($arFields["NAME"]);
		if (isset($arFields["VALUE"]))
			$arFields["VALUE"] = trim($arFields["VALUE"]);
		if (isset($arFields["C_REQUEST"]))
			$arFields["C_REQUEST"] = trim($arFields["C_REQUEST"]);
		if (isset($arFields["C_PHP"]))
			$arFields["C_PHP"] = trim($arFields["C_PHP"]);

		if (isset($arFields["C_SITE"]))
			$arFields["C_SITE"] = (is_array($arFields["C_SITE"]) && $arFields["C_SITE"]) ? '#'.implode('#',$arFields["C_SITE"]).'#' : null;

		if (isset($arFields["C_TEMPLATE"]))
			$arFields["C_TEMPLATE"] = (is_array($arFields["C_TEMPLATE"]) && $arFields["C_TEMPLATE"]) ? '#'.implode('#',$arFields["C_TEMPLATE"]).'#' : null;

		$arFields["ACTIVE"] = $arFields["ACTIVE"] <> "Y" ? "N" : "Y";

		if (isset($arFields["SORT"]))
			$arFields["SORT"] = intval($arFields["SORT"]);

		$arFields["MODIFIED_BY"] = $userId;

		if (isset($arFields["CREATED_BY"]))
			$arFields["CREATED_BY"] = $userId;

		if (isset($arFields["CODE"]) && !$arFields["CODE"]){
			$error[] = Loc::getMessage("ERROR_EMPTY_CODE");
		}

		if (isset($arFields["ACTIVE_FROM"]) && strlen($arFields["ACTIVE_FROM"]) > 0	&& !$DB->IsDate($arFields["ACTIVE_FROM"], false, LANG, "FULL")){
			$error[] = Loc::getMessage("ERROR_BAD_ACTIVE_FROM");
		}

		if (isset($arFields["ACTIVE_TO"]) && strlen($arFields["ACTIVE_TO"]) > 0	&& !$DB->IsDate($arFields["ACTIVE_TO"], false, LANG, "FULL")){
			$error[] = Loc::getMessage("ERROR_BAD_ACTIVE_TO");
		}

		if (isset($arFields["TIMESTAMP_X"]) && strlen($arFields["TIMESTAMP_X"]) > 0	&& !$DB->IsDate($arFields["TIMESTAMP_X"], false, LANG, "FULL")){
			$error[] = Loc::getMessage("ERROR_BAD_TIMESTAMP_X");
		}

		if (isset($arFields["DATE_CREATE"]) && strlen($arFields["DATE_CREATE"]) > 0	&& !$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")){
			$error[] = Loc::getMessage("ERROR_BAD_DATE_CREATE");
		}

		if ($arFields["CODE"]){
			$filter = array('CODE' => $arFields["CODE"]);

			if ($id){
				$filter["!ID"] = $id;
			}

			$beforeRes = self::getList(array(
				'filter' => $filter
			));

			if ($keyRow = $beforeRes->fetch()){
				$error[] = Loc::getMessage("ERROR_DUPLICATE_CODE");
			}
		}

		if (!$error){
			$arFields["TIMESTAMP_X"] = new \Bitrix\Main\Type\DateTime();
			$arFields["DATE_CREATE"] = new \Bitrix\Main\Type\DateTime();
		}

		if ($id){
			unset($arFields["DATE_CREATE"]);
			unset($arFields["CREATED_BY"]);
		}

		return $error;
	}
}