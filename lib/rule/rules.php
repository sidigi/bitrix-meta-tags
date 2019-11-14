<?
namespace Shantilab\MetaTags\Rule;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class RulesTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'shantilab_metatags_rules';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('RULES_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('RULES_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'MODIFIED_BY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('RULES_ENTITY_MODIFIED_BY_FIELD'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('RULES_ENTITY_DATE_CREATE_FIELD'),
			),
			'CREATED_BY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('RULES_ENTITY_CREATED_BY_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('RULES_ENTITY_ACTIVE_FIELD'),
			),
			'C_ALL_KEYS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCAllKeys'),
				'title' => Loc::getMessage('RULES_ENTITY_C_ALL_KEYS_FIELD'),
			),
			'PRIORITY' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validatePriority'),
				'title' => Loc::getMessage('RULES_ENTITY_PRIORITY_FIELD'),
			),
			'C_REQUIRED_KEYS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateRequiredKeys'),
				'title' => Loc::getMessage('RULES_ENTITY_C_REQUIRED_KEYS_FIELD'),
			),
			'ACTIVE_FROM' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('RULES_ENTITY_ACTIVE_FROM_FIELD'),
			),
			'ACTIVE_TO' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('RULES_ENTITY_ACTIVE_TO_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('RULES_ENTITY_SORT_FIELD'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('RULES_ENTITY_NAME_FIELD'),
			),
			'C_SITE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCSite'),
				'title' => Loc::getMessage('RULES_ENTITY_C_SITE_FIELD'),
			),
			'C_TEMPLATE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCTemplate'),
				'title' => Loc::getMessage('RULES_ENTITY_C_TEMPLATE_FIELD'),
			),
			'C_REQUEST' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCRequest'),
				'title' => Loc::getMessage('RULES_ENTITY_C_REQUEST_FIELD'),
			),
			'C_PHP' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCPhp'),
				'title' => Loc::getMessage('RULES_ENTITY_C_PHP_FIELD'),
			),
			'SETTED_KEYS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSettedKeys'),
				'title' => Loc::getMessage('RULES_ENTITY_SETTED_KEYS_FIELD'),
			),
		);
	}
	public static function validateCAllKeys()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}
	public static function validatePriority()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}
	public static function validateRequiredKeys()
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
	public static function validateSettedKeys()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public function checkFieldsDB(&$arFields,$id = null){
		global $DB,$USER;

		$error = array();

		$userId = $USER->getId();

		if (isset($arFields["NAME"]))
			$arFields["NAME"] = trim($arFields["NAME"]);
		if (isset($arFields["C_REQUEST"]))
			$arFields["C_REQUEST"] = trim($arFields["C_REQUEST"]);
		if (isset($arFields["C_PHP"]))
			$arFields["C_PHP"] = trim($arFields["C_PHP"]);

		$arFields["ACTIVE"] = $arFields["ACTIVE"] <> "Y" ? "N" : "Y";

		if ($arFields["C_REQUIRED_KEYS"]){
			$arFields["C_REQUIRED_KEYS"] = str_replace(' ','',$arFields["C_REQUIRED_KEYS"]);
			$arFields["C_REQUIRED_KEYS"] = str_replace('#','',$arFields["C_REQUIRED_KEYS"]);
			$arFields["C_REQUIRED_KEYS"] = explode(',',$arFields["C_REQUIRED_KEYS"]);
			$arFields["C_REQUIRED_KEYS"] = array_unique($arFields["C_REQUIRED_KEYS"]);
			sort($arFields["C_REQUIRED_KEYS"]);
			foreach($arFields["C_REQUIRED_KEYS"] as $index => $val){
				if ($val){
					$arFields["C_REQUIRED_KEYS"][$index] = strtoupper($val);
				}else{
					unset($arFields["C_REQUIRED_KEYS"][$index]);
				}

			}
			$arFields["C_REQUIRED_KEYS"] = implode(',',$arFields["C_REQUIRED_KEYS"]);
		}

		if (!$arFields["C_ALL_KEYS"]){
			$arFields["C_REQUIRED_KEYS"] = NULL;
			$arFields["C_ALL_KEYS"] = "N";
		}
		$arFields["C_ALL_KEYS"] = $arFields["C_ALL_KEYS"] <> "Y" ? "N" : "Y";

		if (isset($arFields["C_SITE"]))
			$arFields["C_SITE"] = (is_array($arFields["C_SITE"]) && $arFields["C_SITE"]) ? '#'.implode('#',$arFields["C_SITE"]).'#' : null;

		if (isset($arFields["C_TEMPLATE"]))
			$arFields["C_TEMPLATE"] = (is_array($arFields["C_TEMPLATE"]) && $arFields["C_TEMPLATE"]) ? '#'.implode('#',$arFields["C_TEMPLATE"]).'#' : null;

		if (isset($arFields["SORT"]))
			$arFields["SORT"] = intval($arFields["SORT"]);

		$arFields["MODIFIED_BY"] = $userId;

		if (isset($arFields["CREATED_BY"]))
			$arFields["CREATED_BY"] = $userId;

		/*GetTagsKeys*/
		$settedKeys = array();
		$realKeys = array();
		$pattern = "|#(.*)#|U";
		if (defined("BX_UTF")){
			$pattern .= 'u';
		}
		foreach($arFields["TEMPLATES"] as $val){
			preg_match_all($pattern,$val["VALUE"], $arOut);
			foreach($arOut[1] as $out){
				if (!is_array($settedKeys)){$settedKeys = array();}

				if (!in_array($out,$settedKeys)){
					$realKeys[] = '/'.$out.'/';
					$settedKeys[] = strtoupper($out);
				}

			}

			if ($val["PAGEN_ON"] == "Y"){
				preg_match_all($pattern,$val["PAGEN"], $arOut);
				foreach($arOut[1] as $out){
					if (!is_array($settedKeys)){$settedKeys = array();}

					if (!in_array($out,$settedKeys)){
						$realKeys[] = '/'.$out.'/';
						$settedKeys[] = strtoupper($out);
					}
				}
			}
		}
		$arFields["SETTED_KEYS"] = implode(',',$settedKeys);
		/**/

		if (isset($arFields["TEMPLATES"])){
			$TEMPLATES = array();
			foreach($arFields["TEMPLATES"] as $val){
				if (!$val["CODE"] && !$val["VALUE"])
					continue;

				$TEMPLATES[] = array(
					"CODE" => strtolower($val["CODE"]),
					"VALUE" => preg_replace($realKeys,$settedKeys,$val["VALUE"]),
					"PAGEN_ON" => ($val["PAGEN_ON"] == "Y") ? "Y" : "N",
					"PAGEN" =>  preg_replace($realKeys,$settedKeys,$val["PAGEN"])
				);
			}
			$arFields["TEMPLATES"] = $TEMPLATES;
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

?>