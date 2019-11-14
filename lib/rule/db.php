<?php
namespace Shantilab\Metatags\Rule;
use Bitrix\Main\Entity\Result;

IncludeModuleLangFile(__FILE__);

class DB extends Base{

	public static function delete($ID){
		$rsEvents = GetModuleEvents("shantilab.metatags", "OnRuleDbDelete");
		while ($arEvent = $rsEvents->Fetch())
		{
			if (ExecuteModuleEvent($arEvent, array($ID)) == false){
				$result = new Result();
				return $result;
			}
		}

		global $DB;
		$DB->StartTransaction();
		$res = RulesTable::delete($ID);
		if (!$res->isSuccess()){
			$DB->Rollback();
		}else{
			$DB->Commit();
		}

		global $CACHE_MANAGER;
		$CACHE_MANAGER->ClearByTag("shantilab_metatags_rules");

		return $res;
	}

	public static function getById($ID){
		$res = RulesTable::getById($ID);
		return $res;
	}

	public static function getPageList($ruleId){
		$res = PagesTable::getList(array(
			"filter" => array("RULE_ID" => $ruleId),
			"order" => array("ID" => "asc")
		));

		return $res;
	}

	public static function getTemplateList($ruleId){
		$res = TemplatesTable::getList(array(
			"filter" => array("RULE_ID" => $ruleId),
			"order" => array("ID" => "asc")
		));

		return $res;
	}

	public static function add($arFields){
		global $DB;
		$result = new Result();

		if (!shanti_prepareRules()){
			$result->addError(new \Bitrix\Main\Entity\EntityError(getMessage("MODULE_ERROR")));
			return $result;
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnRuleAddBefore");
		while ($arEvent = $rsEvents->Fetch())
		{
			if (ExecuteModuleEvent($arEvent, array(&$arFields)) == false){
				return $result;
			}
		}

		$DB->StartTransaction();

		$errors = RulesTable::checkFieldsDB($arFields);
		foreach($errors as $error){
			$result->addError(new \Bitrix\Main\Entity\EntityError($error));
		}
		if (!$result->isSuccess()){
			$DB->Rollback();
			return $result;
		}

		$arPages = array(
			"SHOW_ON" => $arFields["SHOW_ON"],
			"SHOW_OFF" => $arFields["SHOW_OFF"],
		);
		$templates = $arFields["TEMPLATES"];
		array_filter($arPages);
		array_filter($templates);

		unset($arFields["SHOW_ON"]);
		unset($arFields["SHOW_OFF"]);
		unset($arFields["TEMPLATES"]);

		$result = RulesTable::Add($arFields);
		if ($result->isSuccess()){
			$ID = $result->getId();
		}else{
			$DB->Rollback();
			return $result;
		}

		$resultPage = self::AddPageRecords($ID,$arPages);
		if (!$resultPage->isSuccess()){
			foreach($errors = $resultPage->getErrorMessages() as $error){
				$result->addError(new \Bitrix\Main\Entity\EntityError($error));
			}
			$DB->Rollback();
			return $result;
		}

		$resultTemplate = self::AddTemplateRecords($ID,$templates);
		if (!$resultTemplate->isSuccess()){
			foreach($errors = $resultTemplate->getErrorMessages() as $error){
				$result->addError(new \Bitrix\Main\Entity\EntityError($error));
			}
			$DB->Rollback();
			return $result;
		}

		global $CACHE_MANAGER;
		$CACHE_MANAGER->ClearByTag("shantilab_metatags_rules");

		$DB->Commit();

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnRuleAddAfter");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array($ID,$arFields+$arPages+$templates));
		}

		return $result;
	}

	public static function update($ID,$arFields){
		$result = new Result();

		global $DB;

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnRuleUpdateBefore");
		while ($arEvent = $rsEvents->Fetch())
		{
			if (ExecuteModuleEvent($arEvent, array($ID,&$arFields)) == false){
				return $result;
			}
		}

		$DB->StartTransaction();
		$ID = intval($ID);

		if (!$ID || !is_array($arFields)){
			$result->addError(new \Bitrix\Main\Entity\EntityError(getMessage("FIELD_ID_EMPTY")));
			$DB->Rollback();
			return $result;
		}

		$errors = RulesTable::checkFieldsDB($arFields);
		foreach($errors as $error){
			$result->addError(new \Bitrix\Main\Entity\EntityError($error));
		}
		if (!$result->isSuccess()){
			$DB->Rollback();
			return $result;
		}

		$arPages = array(
			"SHOW_ON" => $arFields["SHOW_ON"],
			"SHOW_OFF" => $arFields["SHOW_OFF"],
		);
		$templates = $arFields["TEMPLATES"];
		array_filter($arPages);
		array_filter($templates);

		unset($arFields["SHOW_ON"]);
		unset($arFields["SHOW_OFF"]);
		unset($arFields["TEMPLATES"]);

		$result = RulesTable::update($ID,$arFields);
		if (!$result->isSuccess()){
			$DB->Rollback();
			return $result;
		}

		$resultPage = self::AddPageRecords($ID,$arPages);
		if (!$resultPage->isSuccess()){
			foreach($errors = $resultPage->getErrorMessages() as $error){
				$result->addError(new \Bitrix\Main\Entity\EntityError($error));
			}
			$DB->Rollback();
			return $result;
		}

		$resultTemplate = self::AddTemplateRecords($ID,$templates);
		if (!$resultTemplate->isSuccess()){
			foreach($errors = $resultTemplate->getErrorMessages() as $error){
				$result->addError(new \Bitrix\Main\Entity\EntityError($error));
			}
			$DB->Rollback();
			return $result;
		}

		global $CACHE_MANAGER;
		$CACHE_MANAGER->ClearByTag("shantilab_metatags_rules");

		$DB->Commit();

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnRuleUpdateAfter");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array($ID,$arFields+$arPages+$templates));
		}

		return $result;
	}

	public function AddTemplateRecords($ruleID,$arFields){
		$result = new Result();

		$res = TemplatesTable::getList(array(
			"filter" => array("RULE_ID"=> $ruleID)
		));

		$diff = false;
		$templates = array();
		while ($row = $res->fetch()){
			$recordSet = true;
			$templates[] = array(
				"CODE" => $row["CODE"],
				"VALUE" => $row["VALUE"],
				"PAGEN_ON" => $row["PAGEN_ON"],
				"PAGEN" => $row["PAGEN"],
			);
		}

		if (count($templates) != count($arFields)
			|| !self::identicalValues($templates, $arFields)
			|| !$recordSet
		)
			$diff = true;

		if ($diff){
			self::deleteTemplatesByRule($ruleID);

			foreach($arFields as $template){
				$arItem = array(
					"RULE_ID" => $ruleID,
					"CODE" => strtolower($template["CODE"]),
					"VALUE" => $template["VALUE"],
					"PAGEN_ON" => ($template["PAGEN_ON"] == "Y") ? "Y" : "N",
					"PAGEN" => $template["PAGEN"]
				);
				array_filter($arItem);
				$result = TemplatesTable::Add($arItem);
				if (!$result->isSuccess()){
					return $result;
				}
			}

		}

		return $result;
	}

	protected static function deleteTemplatesByRule($ruleID){
		$connection = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$tableName = TemplatesTable::getTableName();
		if ($tableName && $ruleID)
			$sql = "DELETE FROM $tableName WHERE RULE_ID = '".$sqlHelper->forSql($ruleID)."' ";

		$connection->query($sql);
	}

	public function AddPageRecords($ruleID,$arFields){
		$result = new Result();

		array_filter($arFields);
		if (!isset($arFields["SHOW_ON"]) && !isset($arFields["SHOW_OFF"])){
			return $result;
		}

		$res = PagesTable::getList(array(
			"filter" => array("RULE_ID"=> $ruleID)
		));

		$diff = false;
		$pagesShow = array();
		$pagesNotShow = array();
		while ($row = $res->fetch()){
			$recordSet = true;
			if ($row["SHOW_ON_PAGE"] == "Y"){
				$pagesShow[] = $row["PAGE"];
			}elseif($row["SHOW_ON_PAGE"] == "N"){
				$pagesNotShow[] = $row["PAGE"];
			}
		}

		$errors = self::preparePageFields($arFields);

		foreach($errors as $error){
			$result->addError(new \Bitrix\Main\Entity\EntityError($error));
		}
		if (!$result->isSuccess()){
			return $result;
		}

		if (count($pagesShow) != count($arFields["SHOW_ON"])
			|| count($pagesNotShow) != count($arFields["SHOW_OFF"])
			|| !self::identicalValues($pagesShow, $arFields["SHOW_ON"])
			|| !self::identicalValues($pagesNotShow, $arFields["SHOW_OFF"])
			|| !$recordSet
		)
			$diff = true;

		if ($diff){
			self::deletePagesByRule($ruleID);

			foreach($arFields as $keyField => $typeField){
				foreach($typeField as $url){
					$arItem = array(
						"RULE_ID" => $ruleID,
						"PAGE" => $url,
						"SHOW_ON_PAGE" => ($keyField == 'SHOW_ON') ? 'Y' : "N",
					);
					$result = PagesTable::Add($arItem);
					if (!$result->isSuccess()){
						return $result;
					}
				}
			}

		}

		return $result;
	}

	protected static function deletePagesByRule($ruleID){
		$connection = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$tableName = PagesTable::getTableName();
		if ($tableName && $ruleID)
			$sql = "DELETE FROM $tableName WHERE RULE_ID = '".$sqlHelper->forSql($ruleID)."' ";

		$connection->query($sql);
	}

	public function makeArrayPathFromText(&$arFields){
		$error = array();

		$pattern = "/^\/?([\/\w \.-]*)*\/?$/";
		if (defined("BX_UTF")){
			$pattern .= 'u';
		}
		$arFields = explode("\r\n",$arFields);
		if (count($arFields)){
			$arFields = array_filter($arFields);
			foreach($arFields as $key => $url){
				str_replace(' ', '',$url);
				if (strpos($url,'.') === false && substr($url,'-1') != '/'){
					$url .= '/';
				}

				if (substr($url,0,1) != '/'){
					$url = '/'.$url;
				}

				if (!preg_match($pattern,$url)){
					$error[] = getMessage('ERROR_URL_PATH_ON');
					break;
				}else{
					$arFields[$key] = $url;
				}
			}
		}
		return $error;
	}

	public function preparePageFields(&$arFields){
		$error = array();

		if ($arFields["SHOW_ON"]){
			$error = array_merge($error,self::makeArrayPathFromText($arFields["SHOW_ON"]));
		}
		if ($arFields["SHOW_OFF"]){
			$error = array_merge($error,self::makeArrayPathFromText($arFields["SHOW_OFF"]));
		}

		return $error;
	}

	function identicalValues( $arrayA , $arrayB ) {
		sort( $arrayA );
		sort( $arrayB );
		return $arrayA == $arrayB;
	}

	public static function getList($options){
		$res = RulesTable::getList($options);

		return $res;
	}

	public static function getForPage(){
		$url = \Shantilab\MetaTags\Key\File::getCurdir();

		$cacheId = md5('shantilab_metatags_rules'.$url);
		$cacheDir = "/shantilab.metatags/rules";

		$obCache = new \CPHPCache;
		if($obCache->InitCache(36000, $cacheId, $cacheDir)){
			$rules = $obCache->GetVars();
		}elseif($obCache->StartDataCache() || \CSite::InDir('/bitrix/')){

			$connection = \Bitrix\Main\Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$RulesTableName = RulesTable::getTableName();
			$PagesTableName = PagesTable::getTableName();
			$TemplatesTableName = TemplatesTable::getTableName();


			if ($RulesTableName && $PagesTableName && $TemplatesTableName)
				$sql = "
					SELECT DISTINCT
						R.ID, R.NAME, R.C_ALL_KEYS, R.C_REQUIRED_KEYS,R.SETTED_KEYS, R.C_REQUEST, R.C_PHP, R.PRIORITY

					FROM $RulesTableName R

					LEFT JOIN $PagesTableName P_1 ON (P_1.RULE_ID = R.ID and P_1.SHOW_ON_PAGE='Y')

					LEFT JOIN $PagesTableName P_2 ON (P_2.RULE_ID = R.ID and P_2.SHOW_ON_PAGE='N' and '".$sqlHelper->ForSQL($url)."' like concat(P_2.PAGE, '%'))

					LEFT JOIN $TemplatesTableName T ON (T.RULE_ID = R.ID)

					WHERE
						R.ACTIVE = 'Y'
						and (T.RULE_ID is not null)
						and (P_2.ID is null)
						and (P_1.ID is null or '".$sqlHelper->ForSQL($url)."' like concat(P_1.PAGE, '%'))
						and (R.ACTIVE_FROM <=now() OR R.ACTIVE_FROM IS NULL)
						and (R.ACTIVE_TO >=now() OR R.ACTIVE_TO IS NULL)
						and (R.C_SITE like '#".$sqlHelper->ForSQL(SITE_ID)."#' OR R.C_SITE IS NULL)
						and (R.C_TEMPLATE like '#".$sqlHelper->ForSQL(SITE_TEMPLATE_ID)."#' OR R.C_TEMPLATE IS NULL)
					ORDER BY SORT ASC
						";
			$res = $connection->query($sql);
			$rules = array();

			global $CACHE_MANAGER;
			$CACHE_MANAGER->StartTagCache($cacheDir);

			while($row = $res->fetch()){
				$CACHE_MANAGER->RegisterTag("shantilab_metatags_rules_".$row["ID"]);
				$rules[] = $row;
			}

			$CACHE_MANAGER->RegisterTag("shantilab_metatags_rules");
			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache($rules);
		}

		return $rules;
	}
}