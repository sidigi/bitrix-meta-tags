<?php
namespace Shantilab\Metatags;
IncludeModuleLangFile(__FILE__);

class Tag{

	public static function makeSubstitute($tag){
		$keysOnPage = Key::getList();
		foreach($keysOnPage as $val){
			$pattern = '/#'.$val["CODE"].'#/';
			if (defined("BX_UTF")){
				$pattern .= 'u';
			}
			$keys[] = $pattern;
			$values[] = $val["VALUE"];
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnTagSubstituteBefore");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent,array(&$tag,&$keysOnPage));
		}

		$tag["VALUE_REPLACED"] = preg_replace($keys,$values,$tag["VALUE"]);
		$tag["PAGEN_REPLACED"] = preg_replace($keys,$values,$tag["PAGEN"]);

		$pattern = "|#(.*)#|U";
		if (defined("BX_UTF")){
			$pattern .= 'u';
		}

		$tag["VALUE_REPLACED"] = preg_replace($pattern,'', $tag["VALUE_REPLACED"]);
		$tag["PAGEN_REPLACED"] = preg_replace($pattern,'', $tag["PAGEN_REPLACED"]);

		if ($tag["PAGEN_ON"] == "Y" && Data::isPagen()){
			$tag["FINAL_VALUE"] = trim($tag["VALUE_REPLACED"].' '.$tag["PAGEN_REPLACED"]);
		}else{
			$tag["FINAL_VALUE"] = trim($tag["VALUE_REPLACED"]);
		}

		$rsEvents = GetModuleEvents("shantilab.metatags", "OnTagSubstituteAfter");
		while ($arEvent = $rsEvents->Fetch())
		{
			ExecuteModuleEvent($arEvent,array(&$tag,&$keysOnPage));
		}

		return $tag;
	}

	public static function set($code,$value){
		global $APPLICATION;
		$APPLICATION->arSahntiTags[strtolower($code)] = $value;
	}

	public static function get($code,$default_value = false){
		global $APPLICATION;

		if(isset($APPLICATION->arSahntiTags[strtolower($code)]))
			return $APPLICATION->arSahntiTags[strtolower($code)];

		return $default_value;
	}

	public static function getMeta($code,$meta_name = false, $bXhtmlStyle=true){
		if($meta_name==false)
			$meta_name=$code;

		$val = self::get($code);

		if(!empty($val))
			return '<meta name="'.htmlspecialcharsbx($meta_name).'" content="'.htmlspecialcharsEx($val).'"'.($bXhtmlStyle? ' /':'').'>'."\n";

		return '';
	}

	public static function show($code, $default_value = false)
	{
		global $APPLICATION;
		$APPLICATION->AddBufferContent('ShantiLab\Metatags\Tag::get', $code, $default_value);
	}

	public static function showTitle(){
		global $APPLICATION;
		$APPLICATION->AddBufferContent('ShantiLab\Metatags\Tag::get', 'title');
	}

	public static function showHead($bXhtmlStyle=true){
		self::ShowMeta("robots", false, $bXhtmlStyle);
		self::ShowMeta("keywords", false, $bXhtmlStyle);
		self::ShowMeta("description", false, $bXhtmlStyle);
	}

	public static function showMeta($code, $meta_name=false, $bXhtmlStyle=true){
		global $APPLICATION;
		$APPLICATION->AddBufferContent('ShantiLab\Metatags\Tag::getMeta', $code, $meta_name, $bXhtmlStyle);
	}

	public function getBitrixList(){
		global $APPLICATION;
		$return = array();
		$title = $APPLICATION->getTitle();

		if ($title){
			$return["title1"] = array(
				"CODE" => "title1",
				"VALUE" => $title,
				"TYPE" => "BITRIX",
			);
		}

		foreach($APPLICATION->arPageProperties as $key => $prop){
			if (strtolower($key) == 'title'){
				$key = 'title2';
			}
			$return[strtolower($key)] = array(
				"CODE" => strtolower($key),
				"VALUE" => $prop,
				"TYPE" => "BITRIX",
			);
		}

		return $return;
	}

}