<?php
namespace Shantilab\Metatags;
IncludeModuleLangFile(__FILE__);

class Key extends Key\Base{

	public static function add($fields = array() ){
		if (!$fields || !$fields["CODE"] || !$fields["VALUE"])
			return false;

		$fields = self::validateFields($fields);

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyAdd");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array(&$fields));
		}

		self::$keys[$fields["CODE"]] = $fields;

		return true;
	}

	public static function addGroup($keys = array()){
		if (!$keys)
			return false;

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyAddGroup");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array(&$keys));
		}

		foreach($keys as $key){
			self::add($key);
		}

		return true;
	}

	public static function update($code, $fields){
		if (!$fields || !$code || !$fields["VALUE"])
			return false;

		$fields["CODE"] = $code;

		$fields = self::validateFields($fields);

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyUpdate");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array(&$fields));
		}

		self::$keys[$fields["CODE"]] = $fields;

		return true;
	}

	public static function delete($codes){
		if (!is_array($codes)){
			$codes = array($codes);
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyDelete");
		while ($arEvent = $rsEvents->Fetch())
		{
			 ExecuteModuleEvent($arEvent, array(&$codes));
		}

		if (!$codes)
			self::$keys = array();

		foreach($codes as $code){
			unset(self::$keys[$code]);
		}

		return true;
	}

	public static function getList($keyNames = array()){
		if (!$keyNames){
			return self::getAll();
		}

		if (!is_array($keyNames)){
			$keyNames = array($keyNames);
		}

		$setedKeys = self::getAll();

		$return = array();
		foreach($keyNames as $keyName){
			if ($setedKeys[$keyName]){
				$return[$keyName] = $setedKeys[$keyName];
			}
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeysGetList");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array(&$return));
		}

		return $return;
	}

	protected function getAll(){
		$keysFromDb = Key\DB::getKeysListForPage();
		if ($keysFromDb){
			self::addGroup($keysFromDb);
		}

		self::getFromFiles();

		$keys = self::$keys;

		$return = array();
		foreach($keys as $key){
			if (self::canUse($key)){
				$return[$key["CODE"]] = $key;
			}
		}
		asort($return);
		return $return;
	}

	protected function canUse($key){
		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyCanUse");
		while ($arEvent = $rsEvents->Fetch())
		{
			return ExecuteModuleEvent($arEvent, array(&$key));
		}

		if (isset($key["ACTIVE"]) && $key["ACTIVE"] != "Y")
			return false;

		if (!isset($key["CONDITIONS"]) || !$key["CONDITIONS"] || !is_array($key["CONDITIONS"])){
			return true;
		}

		if (!is_array($key["CONDITIONS"]["SITE"])){$key["CONDITIONS"]["SITE"] = array();}
		if (!is_array($key["CONDITIONS"]["SITE_TEMPLATE_ID"])){$key["CONDITIONS"]["SITE_TEMPLATE_ID"] = array();}

		if ($key["CONDITIONS"]["SITE"] && !in_array(SITE_ID,$key["CONDITIONS"]["SITE"])
			|| $key["CONDITIONS"]["PHP_EXPRESSION"] && !@eval("return ".addslashes($key["CONDITIONS"]["PHP_EXPRESSION"]).";")
			|| $key["CONDITIONS"]["REQUEST"] && !Valid::checkRequestExpression($key["CONDITIONS"]["REQUEST"])
			|| $key["CONDITIONS"]["SITE_TEMPLATE_ID"] && !in_array(SITE_TEMPLATE_ID,$key["CONDITIONS"]["SITE_TEMPLATE_ID"])
			|| $key["CONDITIONS"]["FOLDER"] && !\CSite::InDir($key["CONDITIONS"]["FOLDER"])
		)
			return false;

		return true;
	}

	protected function getFromFiles(){
		$keysFromFile = Key\File::getForPage();
		if ($keysFromFile){
			$keysFromFile = self::prepare($keysFromFile);
			self::addGroup($keysFromFile);
		}

		$keysFromFile = Key\File::getForDir();
		if ($keysFromFile){
			$keysFromFile = self::prepare($keysFromFile);
			self::addGroup($keysFromFile);
		}
	}

	protected function validateFields($key){
		if (isset($key["VALUE"])){
			$key["VALUE"] = trim($key["VALUE"]);
		}
		if (isset($key["CODE"])){
			$key["CODE"] = trim(strip_tags($key["CODE"]));
		}
		if (isset($key["NAME"])){
			$key["NAME"] = trim($key["NAME"]);
		}

		$siteIds = $key["CONDITIONS"]["SITE"];
		if (!is_array($siteIds) && $siteIds)
			$siteIds = array($siteIds);

		$siteTemplateIds = $key["CONDITIONS"]["SITE_TEMPLATE_ID"];
		if (!is_array($siteTemplateIds) && $siteTemplateIds)
			$siteTemplateIds = array($siteTemplateIds);

		if (!isset($key["ACTIVE"]) || !$key["ACTIVE"]){
			$key["ACTIVE"] = "Y";
		}

		if (isset($key["VALUE"]) && $key["VALUE"] && strpos($key["VALUE"], '$') === 0 && strpos($key["VALUE"], ' ') == false){
			$key["VALUE"] = Valid::getVarValFromText($key["VALUE"]);
		}
		if (isset($key["VALUE"]) && $key["VALUE"] && strpos($key["VALUE"], '::') && strpos($key["VALUE"], ' ') == false){
			$key["VALUE"] = @eval("return ".addslashes($key["VALUE"]).";");
		}

		$filterKey = array(
			"ACTIVE" => ($key["ACTIVE"] == "Y" || !isset($key["ACTIVE"])) ? "Y" : "N",
			"CODE" => strtoupper($key["CODE"]),
			"NAME" => $key["NAME"],
			"VALUE" => $key["VALUE"],
			"DESCRIPTION" => $key["DESCRIPTION"],
			"SET_INFO" => array(
				"PATH" => str_replace($_SERVER["DOCUMENT_ROOT"],"",$key["SET_INFO"]["PATH"]),
				"LINK" => $key["SET_INFO"]["LINK"],
			),
			"CONDITIONS" => array(
				"REQUEST" => ($key["CONDITIONS"]["REQUEST"]) ? $key["CONDITIONS"]["REQUEST"] : "",
				"FOLDER" => ($key["CONDITIONS"]["FOLDER"]) ? $key["CONDITIONS"]["FOLDER"] : "",
				"SITE_TEMPLATE_ID" => ($siteTemplateIds) ? $siteTemplateIds : "",
				"SITE" => ($siteIds) ? $siteIds : "",
				"PHP_EXPRESSION" => $key["CONDITIONS"]["PHP_EXPRESSION"],
			)
		);

		if ($key["ID"]){
			$filterKey["ID"] = $key["ID"];
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyValidFields");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array(&$filterKey));
		}

		return $filterKey;
	}

	public static function prepare($keys){
		$return = array();

		if ($keys && is_array($keys) && $keys["CODE"]){
			$keys = array($keys);
		}

		foreach($keys as $key){
			$return[$key["CODE"]] = $key;
		}

		return $return;
	}

	public static function addDefault(){
		$defaultKeys = array();

		$defaultKeys[] = array(
			"CODE" => "SITE_SERVER_NAME",
			"NAME" => getMessage("SITE_SERVER_NAME"),
			"VALUE" => SITE_SERVER_NAME,
		);

		$rsSites = \CSite::GetList($by="sort", $order="desc", Array("ID" => SITE_ID));
		if ($arSite = $rsSites->Fetch())
		{
			$defaultKeys[] = array(
				"CODE" => "SITE_NAME",
				"NAME" => getMessage("SITE_NAME"),
				"VALUE" => $arSite["NAME"]
			);
		}
		$rsTemplates = \CSiteTemplate::GetList(array(), array("ID" => SITE_TEMPLATE_ID));
		if ($arTemplate = $rsTemplates->Fetch())
		{
			$defaultKeys[] = array(
				"CODE" => "TEMPLATE_NAME",
				"NAME" => getMessage("TEMPLATE_NAME"),
				"VALUE" => $arTemplate["NAME"]
			);
		}

		if($pagenKey = self::setPagenKey()){
			$defaultKeys[] = $pagenKey;
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnKeyAddDefaults");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent, array(&$defaultKeys));
		}

		self::addGroup($defaultKeys);
		return;
	}

	public function setPagenKey(){
		$return = array();
		$arUriParams = explode('?',$_SERVER['REQUEST_URI']);
		if ($arUriParams[1]){
			$arUriParams = explode('&',$arUriParams[1]);
			foreach($arUriParams as $param){
				if (strpos($param,'PAGEN_') !== false){
					$arPageNumber = explode('=',$param);
					$pageNumberKey = $arPageNumber[0];
					$pageNumberValue = $arPageNumber[1];
					break;
				}
			}
		}
		if ($pageNumberValue){
			$return = array(
				"CODE"=> $pageNumberKey,
				"NAME"=> getMessage("PAGEN"),
				"VALUE"=> $pageNumberValue,
			);
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnSetPagenKey");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent,array(&$return));
		}

		return $return;
	}
}