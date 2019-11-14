<?php
namespace Shantilab\Metatags;
IncludeModuleLangFile(__FILE__);

class Data{
	protected static $keyEditPage = 'shanti_mtag_edit_key.php';
	protected static $keyListPage = 'shanti_mtag_list_keys.php';
	protected static $ruleListPage = 'shanti_mtag_list_rules.php';
	protected static $ruleEditPage = 'shanti_mtag_edit_rule.php';
	protected static $settedKeys = 'public_setted_keys.php';
	protected static $settedTags = 'public_setted_tags.php';

	public static function getPageName($pageType){
		if($pageType == 'edit_key'){
			return self::$keyEditPage;
		}
		if($pageType == 'list_key'){
			return self::$keyListPage;
		}
		if($pageType == 'list_rule'){
			return self::$ruleListPage;
		}
		if($pageType == 'edit_rule'){
			return self::$ruleEditPage;
		}
		if($pageType == 'setted_keys'){
			return self::$settedKeys;
		}
		if($pageType == 'setted_tags'){
			return self::$settedTags;
		}
		return false;
	}

	public static function getSiteList(){
		$siteList = array();

		$rsSites = \CSite::GetList($by="sort", $order="desc", Array());
		while ($arSite = $rsSites->Fetch()){
			$siteList[] = $arSite;
		}

		return $siteList;
	}

	public static function getTemplateList(){
		$siteTemplateList = array();
		$rsTemplates = \CSiteTemplate::GetList(array('sort' => 'order'), array(), array("ID", "NAME"));
		while($arTemplate = $rsTemplates->Fetch())
		{
			$siteTemplateList[] = $arTemplate;
		}

		return $siteTemplateList;
	}

	public function makeArrayFromHash($arFields){
		if(!$arFields){
			return false;
		}

		$return = implode("\r\n",array_filter(explode("#",$arFields)));

		return $return;
	}

	public function isPagen(){
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
			$return = true;
		}else{
			$return = false;
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnIsPagen");
		while ($arEvent = $rsEvents->Fetch())
		{
			if (ExecuteModuleEvent($arEvent) === true){
				$return = true;
			}
			if (ExecuteModuleEvent($arEvent) === false){
				$return = false;
			}
		}

		return $return;
	}
}