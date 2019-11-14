<?
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/shantilab.metatags/classes/general/menuadmin.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/shantilab.metatags/classes/general/pageevents.php');

function shanti_prepareRules(){
	$cnt = Shantilab\Metatags\Rule\RulesTable::getCount();
	$moduleMode = \CModule::IncludeModuleEx('shantilab.metatags');
	$md = md5('shantilab.metatags_asdfertgts#@$ASEFV ZDFH SRTY#$RFvz');

	if ($moduleMode == 2 &&  $cnt >= 2 &&  $md !== 'dd31713bce506717827d2af12201d16e'){
		return false;
	}
	return true;
}
function shanti_prepareKeys(){
	$cnt = Shantilab\Metatags\Key\KeysTable::getCount();
	$moduleMode = \CModule::IncludeModuleEx('shantilab.metatags');
	$md = md5('shantilab.metatags_asdfertgts#@$ASEFV ZDFH SRTY#$RFvz');

	if ($moduleMode == 2 && $cnt >= 5 && $md !== 'dd31713bce506717827d2af12201d16e'){
		return false;
	}
	return true;
}
?>