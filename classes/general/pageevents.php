<?php
IncludeModuleLangFile(__FILE__);
class shanti_PageEvents{

	function setDefaultKeys()
	{
		if(\COption::GetOptionString('shantilab.metatags','MODULE_ON') == "N" || \CSite::InDir('/bitrix/'))
			return;

		\Shantilab\MetaTags\Key::addDefault();
	}

	function setTags(){

		if(\COption::GetOptionString('shantilab.metatags','MODULE_ON') == "N" || \CSite::InDir('/bitrix/'))
			return;

		$shantiPageInfo["KEYS"] = Shantilab\MetaTags\Key::getList();
		$rule = \Shantilab\MetaTags\Rule::getForPage();
		$shantiPageInfo["RULE"] = $rule;
		$shantiPageInfo["TAGS"]["BITRIX"] = Shantilab\Metatags\Tag::getBitrixList();
		$settedTags = $shantiPageInfo["TAGS"]["BITRIX"];

		if ($rule){
			$priority = \COption::GetOptionString('shantilab.metatags','PRIORITY');

			if ($rule["PRIORITY"] == "Y"){
				$priority = "MODULE";
			}
			if ($rule["PRIORITY"] == "N"){
				$priority = "BITRIX";
			}

			$title1 = \COption::GetOptionString('shantilab.metatags','TITLE_1');
			$title2 = \COption::GetOptionString('shantilab.metatags','TITLE_2');

			global $APPLICATION;

			foreach($rule["TAGS"] as $tag){
				if (!$tag["CODE"] || !$tag["FINAL_VALUE"]){
					continue;
				}
				$arInfo = array();

				if (toUpper($priority) == "MODULE"){

					if (($title1 == "Y" && toLower($tag["CODE"]) == 'title') || toLower($tag["CODE"]) == 'title1'){
						$APPLICATION->SetTitle($tag["FINAL_VALUE"]);
						$arInfo[] = array(
							"CODE" => 'title1',
							"VALUE" => $tag["FINAL_VALUE"]
						);
					}

					if((toLower($tag["CODE"]) == 'title' && $title2 == "Y") || toLower($tag["CODE"]) == 'title2'){
						$APPLICATION->SetPageProperty('title', $tag["FINAL_VALUE"]);
						$arInfo[] = array(
							"CODE" => 'title2',
							"VALUE" => $tag["FINAL_VALUE"]
						);
					}

					if(!in_array(toLower($tag["CODE"]), array('title','title1','title2'))){
						$arInfo[] = array(
							"CODE" => toLower($tag["CODE"]),
							"VALUE" => $tag["FINAL_VALUE"]
						);
					}

				}elseif(toUpper($priority) == "BITRIX"){

					if ((toLower($tag["CODE"]) == 'title' && $title1 == "Y") || toLower($tag["CODE"]) == 'title1')
						$titleVal1 = $APPLICATION->GetTitle($tag["FINAL_VALUE"]);
					if ((toLower($tag["CODE"]) == 'title' && $title2 == "Y") || toLower($tag["CODE"]) == 'title2')
						$titleVal2 = $APPLICATION->GetProperty('title');
					if (!in_array(toLower($tag["CODE"]), array('title','title1','title2')))
						$bitrixTag = $APPLICATION->GetProperty($tag["CODE"]);

					if (!$titleVal1 && (toLower($tag["CODE"]) == 'title' || toLower($tag["CODE"]) == 'title1')){
						$APPLICATION->SetTitle($tag["FINAL_VALUE"]);
						$arInfo[] = array(
							"CODE" => 'title1',
							"VALUE" => $tag["FINAL_VALUE"]
						);
					}

					if (!$titleVal2 && (toLower($tag["CODE"]) == 'title' || toLower($tag["CODE"]) == 'title2')){
						$APPLICATION->SetPageProperty('title', $tag["FINAL_VALUE"]);
						$arInfo[] = array(
							"CODE" => 'title2',
							"VALUE" => $tag["FINAL_VALUE"]
						);
					}
					if (!$bitrixTag && !in_array(toLower($tag["CODE"]), array('title','title1','title2'))){
						$APPLICATION->SetPageProperty($tag["CODE"], $bitrixTag);
						$arInfo[] = array(
							"CODE" => toLower($tag["CODE"]),
							"VALUE" => $bitrixTag
						);
					}
				}
				foreach($arInfo as $inf){
					$settedTags[toLower($inf["CODE"])]["TYPE"] = "MODULE";
					$settedTags[toLower($inf["CODE"])]["VALUE"] = $inf["VALUE"];
					$settedTags[toLower($inf["CODE"])]["MASK"] = ($tag["PAGEN_ON"] == "Y" && Shantilab\MetaTags\Data::isPagen()) ? $tag["VALUE"].$tag["PAGEN"] : $tag["VALUE"];
					$settedTags[toLower($inf["CODE"])]["CODE"] = toLower($inf["CODE"]);
				}

				\Shantilab\MetaTags\Tag::set(toLower($tag["CODE"]),$tag["FINAL_VALUE"]);
			}
		}
		$shantiPageInfo["TAGS"]["SETTED"] = $settedTags;
		file_put_contents($_SERVER["DOCUMENT_ROOT"].'/upload/shantilab.metatags/pageinfo.txt',serialize($shantiPageInfo));
	}
}