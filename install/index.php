<?
IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/shantilab.metatags/include.php');

if (class_exists("shantilab_metatags"))
	return;

Class shantilab_metatags extends CModule
{
	var $MODULE_ID = "shantilab.metatags";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";

	var $PARTNER_NAME;
	var $PARTNER_URI;

	function shantilab_metatags()
	{
		$arModuleVersion = array();
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("META_TAGS_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("META_TAGS_MODULE_DESCRIPTION");

		$this->PARTNER_NAME = GetMessage("PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("PARTNER_URI");
	}

	function UnInstallDB($arParams = array()){
		global $APPLICATION, $DB;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shantilab.metatags/install/db/".strtolower($DB->type)."/uninstall.sql");

			if (!empty($this->errors))
			{
				$APPLICATION->ThrowException(implode("", $this->errors));
				return false;
			}
		}

		UnRegisterModule("shantilab.metatags");

		return true;
	}

	function InstallDB($arParams = array()){
		global $DB, $APPLICATION;
		$this->errors = false;

		if(!$DB->Query("SELECT 'x' FROM shant_tags_keys", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/shantilab.metatags/install/db/".strtolower($DB->type)."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		RegisterModule("shantilab.metatags");

		return true;
	}

	function InstallFiles(){
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shantilab.metatags/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shantilab.metatags/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true,true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shantilab.metatags/install/panel/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/panel",true,true);
		mkdir($_SERVER["DOCUMENT_ROOT"].'/upload/shantilab.metatags/');
		return true;
	}

	function UnInstallFiles(){
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shantilab.metatags/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFilesEx("/bitrix/components/shantilab/iblock.keys.autoset");
		DeleteDirFilesEx("/bitrix/panel/shantilab.metatags");
		rmdir($_SERVER["DOCUMENT_ROOT"].'/upload/shantilab.metatags/');
		return true;
	}

	function InstallEvents(){
		RegisterModuleDependences("main", "OnBuildGlobalMenu", "shantilab.metatags", "shanti_TagsAdminMenu", "OnCreateMenuItems");
		RegisterModuleDependences("main", "OnProlog", "shantilab.metatags", "shanti_PageEvents", "setDefaultKeys");
		RegisterModuleDependences("main", "OnPanelCreate", "shantilab.metatags", "shanti_TagsAdminMenu", "OnPanelCreateButtons");
		RegisterModuleDependences("main", "OnEpilog", "shantilab.metatags", "shanti_PageEvents", "setTags");
		return true;
	}
	function UnInstallEvents(){
		UnRegisterModuleDependences("main", "OnBuildGlobalMenu", "shantilab.metatags", "shanti_TagsAdminMenu", "OnCreateMenuItems");
		UnRegisterModuleDependences("main", "OnProlog", "shantilab.metatags", "shanti_PageEvents", "setDefaultKeys");
		UnRegisterModuleDependences("main", "OnPanelCreate", "shantilab.metatags", "shanti_TagsAdminMenu", "OnPanelCreateButtons");
		UnRegisterModuleDependences("main", "OnProlog", "shantilab.metatags", "shanti_PageEvents", "setTags");
		return true;
	}

	function DoInstall()
	{
		global $USER;

		if ($USER->IsAdmin())
		{
			$this->InstallDB();
			$this->InstallEvents();
			$this->InstallFiles();
		}
	}

	function DoUninstall()
	{
		global $APPLICATION,$USER,$step,$errors;

		if ($USER->IsAdmin())
		{
			$step = IntVal($step);

			if ($step < 2)
			{
				$errors = false;

				$APPLICATION->IncludeAdminFile(
					GetMessage("TAGS_DELETE_TITLE"),
					$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shantilab.metatags/install/uninst1.php"
				);
			}
			elseif ($step == 2)
			{
				$errors = false;

				$this->UnInstallEvents();

				$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
				));

				$this->UnInstallFiles();

				$APPLICATION->IncludeAdminFile(
					GetMessage("TAGS_DELETE_TITLE"),
					$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shantilab.metatags/install/uninst2.php"
				);
			}
		}
	}
}