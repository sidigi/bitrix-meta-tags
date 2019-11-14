<?php
namespace Shantilab\Metatags;
IncludeModuleLangFile(__FILE__);

class Rule extends Rule\Base{

	public function getForPage(){
		$rules = Rule\DB::getForPage();

		if ($rules){
			$rules = self::filter($rules);
		}

		return $rules;
	}

	protected function filter($rules){
		if (!$rules || !is_array($rules))
			return false;

		if ($rules && !is_array($rules)){
			$rules = array($rules);
		}

		foreach($rules as $index => $rule){
			if ($rule["C_PHP"] && !@eval("return ".addslashes($rule["C_PHP"]).";")
				|| $rule["C_REQUEST"] && !Valid::checkRequestExpression($rule["C_REQUEST"])
				|| $rule["C_ALL_KEYS"] == 'Y' && $rule["SETTED_KEYS"] && !self::checkKeys($rule["SETTED_KEYS"])
				|| $rule["C_ALL_KEYS"] == 'N' && $rule["C_REQUIRED_KEYS"] && !self::checkKeys($rule["C_REQUIRED_KEYS"])
				){
				unset($rules[$index]);
			}
		}

		if ($rules){
			reset($rules);
			$rule = current($rules);
			$tagsRes = Rule\TemplatesTable::getlist(array(
				"filter" => array("RULE_ID" => $rule["ID"]),
				"order" => array("CODE" => "ASC", "ID" => "DESC")
			));

			$rule = array(
				"ID" => $rule["ID"],
				"NAME" => $rule["NAME"],
				"PRIORITY" => $rule["PRIORITY"],
			);

			while($tag = $tagsRes->fetch()){
				$tag = \Shantilab\MetaTags\Tag::makeSubstitute($tag);
				$rule["TAGS"][$tag["CODE"]] = $tag;
			}
		}else{
			$rule = false;
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnRuleGet");
		while ($arEvent = $rsEvents->Fetch())
		{
			$rule = ExecuteModuleEvent($arEvent, array(&$rule));
		}

		return $rule;
	}

	public function getCurDir(){
		global $APPLICATION;

		$sRealFilePath = $_SERVER["REAL_FILE_PATH"];

		if (strlen($sRealFilePath) > 0)
		{
			$slash_pos = strrpos($sRealFilePath, "/");
			$sFilePath = substr($sRealFilePath, 0, $slash_pos+1);
		}
		else
		{
			$sFilePath = $APPLICATION->GetCurPage(true);
		}

		return $sFilePath;
	}

	protected static function checkKeys($keys){
		$keys = explode(',',$keys);
		if (!$keys){
			return false;
		}

		$keysOnPage = Key::getList();
		if (!$keysOnPage){
			return true;
		}

		$keysOnPage = array_keys($keysOnPage);

		$result = array_diff($keys, $keysOnPage);

		if ($result){
			return false;
		}

		return true;

	}
}