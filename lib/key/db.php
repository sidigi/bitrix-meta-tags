<?php
namespace Shantilab\Metatags\Key;
use Bitrix\Main\Entity\Result;

IncludeModuleLangFile(__FILE__);

class DB extends Base{

	public static function delete($ID){
		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyDbDelete");
		while ($arEvent = $rsEvents->Fetch())
		{
			if (ExecuteModuleEvent($arEvent, array($ID)) == false){
				$result = new Result();
				return $result;
			}
		}

		global $DB;
		$DB->StartTransaction();

		$res = KeysTable::delete($ID);
		if (!$res->isSuccess()){
			$DB->Rollback();
		}else{
			$DB->Commit();
		}

		global $CACHE_MANAGER;
		$CACHE_MANAGER->ClearByTag("shantilab_metatags_keys");
		return $res;
	}

	public static function getById($ID){
		$res = KeysTable::getById($ID);
		return $res;
	}

	public static function add($arFields){
		global $DB;
		$result = new Result();

		if (!shanti_prepareKeys()){
			$result->addError(new \Bitrix\Main\Entity\EntityError(getMessage("MODULE_ERROR")));
			return $result;
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyDbAddBefore");
		while ($arEvent = $rsEvents->Fetch())
		{
			if (ExecuteModuleEvent($arEvent, array(&$arFields)) == false){
				return $result;
			}
		}

		$DB->StartTransaction();

		$arPages = array(
			"SHOW_ON" => $arFields["SHOW_ON"],
			"SHOW_OFF" => $arFields["SHOW_OFF"],
		);
		array_filter($arPages);

		unset($arFields["SHOW_ON"]);
		unset($arFields["SHOW_OFF"]);
		$arKey = $arFields;

		$errors = KeysTable::checkFieldsDB($arKey);

		foreach($errors as $error){
			$result->addError(new \Bitrix\Main\Entity\EntityError($error));
		}
		if (!$result->isSuccess()){
			$DB->Rollback();
			return $result;
		}

		$result = KeysTable::Add($arKey);
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

		global $CACHE_MANAGER;
		$CACHE_MANAGER->ClearByTag("shantilab_metatags_keys");

		$DB->Commit();

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyDbAddAfter");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array($ID,$arFields+$arPages));
		}

		return $result;
	}

	public static function update($ID,$arFields){
		$result = new Result();
		global $DB;

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyDbUpdateBefore");
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

		$arPages = array(
			"SHOW_ON" => $arFields["SHOW_ON"],
			"SHOW_OFF" => $arFields["SHOW_OFF"],
		);
		array_filter($arPages);

		unset($arFields["SHOW_ON"]);
		unset($arFields["SHOW_OFF"]);
		$arKey = $arFields;

		$errors = KeysTable::checkFieldsDB($arKey,$ID);

		foreach($errors as $error){
			$result->addError(new \Bitrix\Main\Entity\EntityError($error));
		}
		if (!$result->isSuccess()){
			$DB->Rollback();
			return $result;
		}

		$result = KeysTable::Update($ID,$arKey);
		if (!$result->isSuccess()){
			$DB->Rollback();
			return $result;
		}

		$result = self::AddPageRecords($ID,$arPages);
		if (!$result->isSuccess()){
			$DB->Rollback();
			return $result;
		}

		global $CACHE_MANAGER;
		$CACHE_MANAGER->ClearByTag("shantilab_metatags_keys");

		$DB->Commit();

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyDbUpdateAfter");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array($ID,$arFields+$arPages));
		}

		return $result;
	}

	public static function getList($options){
		$res = KeysTable::getList($options);

		return $res;
	}

	public static function getKeysListForPage(){
		$url = File::getCurdir();

		$cacheId = md5('shantilab_metatags_keys'.$url);
		$cacheDir = "/shantilab.metatags/keys";

		$obCache = new \CPHPCache;

		if($obCache->InitCache(36000, $cacheId, $cacheDir)){
			$keys = $obCache->GetVars();
		}elseif($obCache->StartDataCache() || \CSite::InDir('/bitrix/')){

			$connection = \Bitrix\Main\Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$KeysTableName = KeysTable::getTableName();
			$PagesTableName = PagesTable::getTableName();

			if ($KeysTableName && $PagesTableName)
				$sql = "
				SELECT DISTINCT
					K.ID, ACTIVE, SORT, CODE, NAME, VALUE, C_SITE, C_TEMPLATE, C_REQUEST, C_PHP

				FROM $KeysTableName K

				LEFT JOIN $PagesTableName P_1 ON (P_1.KEY_ID = K.ID	and P_1.SHOW_ON_PAGE='Y')

				LEFT JOIN $PagesTableName P_2 ON (P_2.KEY_ID = K.ID	and P_2.SHOW_ON_PAGE='N' and '".$sqlHelper->ForSQL($url)."' like concat(P_2.PAGE, '%'))

				WHERE
					K.ACTIVE = 'Y'
					and (P_2.ID is null)
					and (P_1.ID is null or '".$sqlHelper->ForSQL($url)."' like concat(P_1.PAGE, '%'))
					and (K.ACTIVE_FROM <=now() OR K.ACTIVE_FROM IS NULL)
					and (K.ACTIVE_TO >=now() OR K.ACTIVE_TO IS NULL)
					and (K.C_SITE like '#".$sqlHelper->ForSQL(SITE_ID)."#'  OR K.C_SITE IS NULL)
					and (K.C_TEMPLATE like '#".$sqlHelper->ForSQL(SITE_TEMPLATE_ID)."#' OR K.C_TEMPLATE IS NULL)
					and (K.VALUE IS NOT NULL)
					and (K.CODE IS NOT NULL)
				ORDER BY SORT ASC
					";
			$res = $connection->query($sql);
			$keys = array();
			while($row = $res->fetch()){
				$row = self::prepareDbKeys($row);
				if ($row){
					$keys[$row["CODE"]] = $row;
				}
			}

			global $CACHE_MANAGER;
			$CACHE_MANAGER->StartTagCache($cacheDir);
			$CACHE_MANAGER->RegisterTag("shantilab_metatags_keys");
			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache($keys);

		}
		return $keys;
	}

	public static function getPageList($keyId){
		$res = PagesTable::getList(array(
			"filter" => array("KEY_ID" => $keyId),
			"order" => array("ID" => "asc")
		));

		return $res;
	}

	public function AddPageRecords($keyID,$arFields){
		$result = new Result();

		array_filter($arFields);
		if (!isset($arFields["SHOW_ON"]) && !isset($arFields["SHOW_OFF"])){
			return $result;
		}

		$res = PagesTable::getList(array(
			"filter" => array("KEY_ID"=> $keyID)
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
			self::deletePagesByKey($keyID);

			foreach($arFields as $keyField => $typeField){
				foreach($typeField as $url){
					$arItem = array(
						"KEY_ID" => $keyID,
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

	protected static function deletePagesByKey($keyID){
		$connection = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$tableName = PagesTable::getTableName();
		if ($tableName && $keyID)
			$sql = "DELETE FROM $tableName WHERE KEY_ID = '".$sqlHelper->forSql($keyID)."' ";

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

	protected function prepareDbKeys($key){
		if (!$key || !$key["CODE"] || !$key["VALUE"]){
			return false;
		}

		if ($key["C_SITE"]){
			$siteIds = $key["C_SITE"];
			$siteIds = explode("#",$siteIds);
			trimArr($siteIds);
			$siteIds = array_values($siteIds);
		}

		if ($key["C_TEMPLATE"]){
			$siteTemplateIds = $key["C_TEMPLATE"];
			$siteTemplateIds = explode("#",$siteTemplateIds);
			trimArr($siteTemplateIds);
			$siteTemplateIds= array_values($siteTemplateIds);
		}
		
		$return = array(
			"ID" => $key["ID"],
			"ACTIVE" => ($key["ACTIVE"] == "Y" || !isset($key["ACTIVE"])) ? "Y" : "N",
			"CODE" => $key["CODE"],
			"NAME" => $key["NAME"],
			"VALUE" => $key["VALUE"],
			"SET_INFO" => array(
				"LINK" => "/bitrix/admin/".\Shantilab\Metatags\Data::getPageName('edit_key')."?ID={$key["ID"]}",
			),
			"CONDITIONS" => array(
				"REQUEST" => ($key["C_REQUEST"]) ? $key["C_REQUEST"] : "",
				"PHP_EXPRESSION" => $key["C_PHP"],
				"SITE_TEMPLATE_ID" => ($siteTemplateIds) ? $siteTemplateIds : "",
				"SITE" => ($siteIds) ? $siteIds : "",
			)
		);

		return $return;
	}

}