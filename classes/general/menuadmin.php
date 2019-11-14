<?php
IncludeModuleLangFile(__FILE__);

class shanti_TagsAdminMenu{

	function OnCreateMenuItems(&$aGlobalMenu, &$aModuleMenu)
	{
		global $APPLICATION;
		$APPLICATION->SetAdditionalCSS('/bitrix/panel/shantilab.metatags/shanti_seo.css');

		$TAGS_RIGHT = $APPLICATION->GetGroupRight('shantilab.metatags');
		if ($TAGS_RIGHT == "D")
			return;

		$metatagsMenu = array(
			"parent_menu" => "global_menu_services",
            "section" => "metatags",
            "sort" => 90,
            "text" => getMessage('MAIN_TAG_MENU_TEXT'),
            "title" => getMessage('MAIN_TAG_MENU_TITLE'),
            "icon" => "shanti_seo_menu",
            "page_icon" => "shanti_seo_menu",
            "module_id" => "shantilab.metatags",
            "items_id" => "shantilab.metatags",
            "items" => Array(
	            array(
		            "url" => Shantilab\MetaTags\Data::getPageName('list_key')."?lang=".LANG,
		            "more_url" => array(Shantilab\MetaTags\Data::getPageName('list_key'),Shantilab\MetaTags\Data::getPageName('edit_key')),
		            "text" => getMessage('MAIN_TAG_MENU_KEYS'),
	            ),
	            array(
		            "url" => Shantilab\MetaTags\Data::getPageName('list_rule')."?lang=".LANG,
		            "more_url" => array(Shantilab\MetaTags\Data::getPageName('list_rule'),Shantilab\MetaTags\Data::getPageName('edit_rule')),
		            "text" => getMessage('MAIN_TAG_MENU_RULES'),
	            ),
            ),
		);

		foreach($aModuleMenu as $k => $v)
		{
			if($v['parent_menu'] == 'global_menu_services' && $v['items_id']=='menu_seo')
			{
				array_splice($aModuleMenu, $k+1, 0, array($metatagsMenu));
				break;
			}
		}
	}

	function OnPanelCreateButtons()
	{
		global $APPLICATION;
		$TAGS_RIGHT = $APPLICATION->GetGroupRight('shantilab.metatags');
		if ($TAGS_RIGHT == "D")
			return;

		if($APPLICATION->arPanelButtons["edit"]["ID"] == "edit" && $TAGS_RIGHT == "W"){
			$APPLICATION->arPanelButtons["edit"]["MENU"][] = array(
				"SEPARATOR" => true,
				"SORT" => 999
			);
			$pageItem = array(
				"TEXT" => getMessage("MENU_KEY_PAGE_TITLE"),
				"TITLE" => getMessage("MENU_KEY_PAGE_TITLE"),
				"ICON" => "panel-key",
				"ACTION" => "(new BX.CAdminDialog({'content_url':'/bitrix/admin/".Shantilab\Metatags\Data::getPageName("edit_key")."?lang=ru&bxpublic=Y&&path=".urlencode($APPLICATION->getCurPage(true))."&site=".SITE_ID."&back_url=".urlencode($APPLICATION->getCurPageParam())."','width':'700','height':'400'})).Show()",
				"SORT" => 1000,
			);
			$APPLICATION->arPanelButtons["edit"]["MENU"][] = $pageItem;
		}

		if($APPLICATION->arPanelButtons["edit_section"]["ID"] == "edit_section" && $TAGS_RIGHT == "W"){
			$APPLICATION->arPanelButtons["edit_section"]["MENU"][] = array(
				"SEPARATOR" => true,
				"SORT" => 999
			);
			$sectionItem = array(
				"TEXT" => getMessage("MENU_KEY_DIR_TITLE"),
				"TITLE" => getMessage("MENU_KEY_DIR_TITLE"),
				"ICON" => "panel-key",
				"ACTION" => "(new BX.CAdminDialog({'content_url':'/bitrix/admin/".Shantilab\Metatags\Data::getPageName("edit_key")."?lang=ru&bxpublic=Y&path=".urlencode($APPLICATION->getCurDir(true))."&site=".SITE_ID."&back_url=".urlencode($APPLICATION->getCurPageParam())."','width':'700','height':'400'})).Show()",
				"SORT" => 1000,
			);
			$APPLICATION->arPanelButtons["edit_section"]["MENU"][] = $sectionItem;
		}

		$arMenu = array();
		$arMenu[] = array(
			"TEXT"  => getMessage("MENU_KEY_SHOW_TITLE"),
			"TITLE"  => getMessage("MENU_KEY_SHOW_TITLE"),
			"ICON"  => "panel-show-keys",
			"ACTION" => "(new BX.CDialog({'content_url':'/bitrix/admin/".Shantilab\Metatags\Data::getPageName("setted_keys")."?lang=ru&site=".SITE_ID."&path=".$APPLICATION->GetCurPage(true)."&back_url=".$APPLICATION->GetCurPageParam()."','width':'1000','height':'600'})).Show()",
			"DEFAULT" => false,
		);

		$arMenu[] = array(
			"TEXT"  => getMessage("MENU_TAGS_SHOW_TITLE"),
			"TITLE"  => getMessage("MENU_TAGS_SHOW_TITLE"),
			"ICON"  => "panel-show-tags",
			"ACTION" => "(new BX.CDialog({'content_url':'/bitrix/admin/".Shantilab\Metatags\Data::getPageName("setted_tags")."?lang=ru&site=".SITE_ID."&path=".$APPLICATION->GetCurPage(true)."&back_url=".$APPLICATION->GetCurPageParam()."','width':'1000','height':'600'})).Show()",
			"DEFAULT" => false,
		);

		$arMenu[] = array('SEPARATOR' => "Y");

		$arMenu[] = $pageItem;
		$arMenu[] = $sectionItem;

		$APPLICATION->SetAdditionalCSS('/bitrix/panel/shantilab.metatags/shanti_seo.css');

		$APPLICATION->AddPanelButton(array(
			"HREF"      => "",
			"SRC"       => "/bitrix/panel/shantilab.metatags/images/light_blue_button_17x10.png",
			"ALT"       => getMessage("MENU_MODULE_BUTTON_TITLE"),
			"TITLE"     => getMessage("MENU_MODULE_BUTTON_TITLE"),
			"TEXT"      => getMessage("MENU_MODULE_BUTTON_TITLE"),
			"MAIN_SORT" => 4000,
			"SORT"      => 100,
			"MENU" => $arMenu,
		));
	}
}
