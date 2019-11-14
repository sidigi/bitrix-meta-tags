<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shantilab.metatags/prolog.php");
$moduleMode = \CModule::IncludeModuleEx(ADMIN_MODULE_NAME);
IncludeModuleLangFile(__FILE__);

if (!$moduleMode){
	echo GetMessage("MODULE_IS_NOT_INSTALL");
	return;
}

$TAGS_RIGHT = $APPLICATION->GetGroupRight(ADMIN_MODULE_NAME);
if ($TAGS_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => getMessage("KEY_TAB_NAME"), "ICON"=>"", "TITLE"=>getMessage("KEY_TAB_NAME")),
	array("DIV" => "edit2", "TAB" => getMessage("KEY_CONDITIONS_TAB_NAME"), "ICON"=>"", "TITLE" => getMessage("KEY_CONDITIONS_TAB_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);
$message = null;
$bVarsFromForm = false;

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

$bCopy = ($request->get('action') == "copy");

if ($request->getRequestMethod() == 'GET' && $request->getQuery('action') === 'delete' && check_bitrix_sessid())
{
	$res = Shantilab\MetaTags\Key\DB::delete($ID);

	if ($res->isSuccess()){
		LocalRedirect("/bitrix/admin/".Shantilab\Metatags\Data::getPageName('list_key')."?lang=".LANG);
	}else{
		$message = new CAdminMessage(implode("<br>", $res->getErrorMessages()));
	}

}

if($request->isPost() && ($save != "" || $apply!="") && check_bitrix_sessid()){

	if ($ACTIVE_FROM)
		$ACTIVE_FROM = new \Bitrix\Main\Type\DateTime($ACTIVE_FROM);
	if ($ACTIVE_TO)
		$ACTIVE_TO = new \Bitrix\Main\Type\DateTime($ACTIVE_TO);

	$arFields = Array(
		"ACTIVE"         => $ACTIVE,
		"ACTIVE_FROM"    => $ACTIVE_FROM,
		"ACTIVE_TO"      => $ACTIVE_TO,
		"SORT"           => $SORT,
		"CODE"           => $CODE,
		"NAME"           => $NAME,
		"VALUE"          => $VALUE,
		"C_SITE"         => $arrSITE,
		"C_TEMPLATE"     => $arrTEMPLATE,
		"C_REQUEST"      => $C_REQUEST,
		"C_PHP"          => $C_PHP,
		"SHOW_ON"        => $SHOW_ON,
		"SHOW_OFF"       => $SHOW_OFF,
	);

	if($ID > 0)
	{
		if ($bCopy){
			$res = Shantilab\MetaTags\Key\DB::add($arFields);
			if ($res->isSuccess()){
				$ID = $res->getId();
			}
		}else{
			$res = Shantilab\MetaTags\Key\DB::update($ID,$arFields);
		}

		if ($res->isSuccess()){
			$success = true;
		}else{
			$message = new CAdminMessage(implode("<br>", $res->getErrorMessages()));
		}
	}
	else
	{
		$res = Shantilab\MetaTags\Key\DB::Add($arFields);
		if ($res->isSuccess()){
			$ID = $res->getId();
			$success = true;
		}else{
			$message = new CAdminMessage(implode("<br>", $res->getErrorMessages()));
		}
	}

	if($success)
	{
		if ($apply != ""){
			LocalRedirect("/bitrix/admin/".Shantilab\Metatags\Data::getPageName('edit_key')."?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
		}
		else{
			if($request->get('back_url')){
				LocalRedirect($request->get('back_url'));
			}
			LocalRedirect("/bitrix/admin/".Shantilab\Metatags\Data::getPageName('list_key')."?lang=".LANG);
		}

	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("KEY_ERROR_SAVE"), $e);
		$bVarsFromForm = true;
	}
}

$dataKeysMap = Shantilab\MetaTags\Key\KeysTable::getMap();
$siteList = Shantilab\MetaTags\Data::getSiteList();
$siteTemplateList = Shantilab\MetaTags\Data::getTemplateList();

$arKey = array();
$arKey["ACTIVE"]        = "Y";
$arKey["SORT"]          = 500;

if($ID > 0)
{
	$res = Shantilab\MetaTags\Key\DB::getById($ID);

	if($arKey = $res->fetch()){
		if ($arKey["C_SITE"]){
			$arKey["arrSITE"] = array_filter(explode("#",$arKey["C_SITE"]));
		}
		if ($arKey["C_TEMPLATE"]){
			$arKey["arrTEMPLATE"] = array_filter(explode("#",$arKey["C_TEMPLATE"]));
		}

		$res = Shantilab\MetaTags\Key\DB::getPageList($ID);
		$arKey["SHOW_ON"] = '';
		$arKey["SHOW_OFF"] = '';
		while($arTmp = $res->fetch()){
			if ($arTmp["SHOW_ON_PAGE"] == "Y"){
				$arKey["SHOW_ON"] .= $arTmp["PAGE"]."\r\n";
			}else{
				$arKey["SHOW_OFF"] .= $arTmp["PAGE"]."\r\n";
			}
		}
	}else{
		$ID = 0;
	}
}

if($bVarsFromForm){
	$arKey = $request->toArray();
}

if(!$ID && $request->get("path")){
	$arKey["SHOW_ON"] = $request->get("path");
	$arKey["arrSITE"] = array('s1');
}


if (!is_array($arKey["arrSITE"])){
	$arKey["arrSITE"] = array();
}

if (!is_array($arKey["arrSITE"])){
	$arKey["arrTEMPLATE"] = array();
}


$APPLICATION->SetTitle(( $ID > 0 ? GetMessage("KEY_TITLE_EDIT").$ID : GetMessage("KEY_TITLE_ADD")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($moduleMode == 2){
	echo BeginNote();
	echo GetMessage("DEMO_INFO");
	echo EndNote();
}

$aMenu = array();
$aMenu[] = array(
		"TEXT"  => GetMessage("KEYS_TITLE"),
		"TITLE" => GetMessage("KEYS_TITLE"),
		"LINK"  => Shantilab\Metatags\Data::getPageName('list_key')."?lang=".LANG,
		"ICON"  => "btn_list",
	);

if($ID > 0){
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"=>GetMessage("COPY_KEY"),
		"TITLE"=>GetMessage("COPY_KEY"),
		"LINK"=> Shantilab\Metatags\Data::getPageName('edit_key')."?ID=".$ID."&action=copy&lang=".LANG,
		"ICON"=>"btn_copy",
	);

	$arSubMenu = array();
	$arSubMenu[] = array(
		"TEXT"  => GetMessage("KEY_ADD"),
		"TITLE" => GetMessage("KEY_ADD"),
		"LINK"  => Shantilab\Metatags\Data::getPageName('edit_key')."?lang=".LANG,
		"ICON"  => "new",
	);
	$arSubMenu[] = array(
		"TEXT"  => GetMessage("KEY_DEL"),
		"TITLE" => GetMessage("KEY_DEL"),
		"LINK"  => "javascript:if(confirm('".GetMessage("KEY_DEL_CONFIRM")."')) ".
			"window.location='".Shantilab\Metatags\Data::getPageName('edit_key')."?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"  => "delete",
	);

	$aMenu[] = array(
		"TEXT"=>GetMessage("KEY_ACTIONS"),
		"TITLE"=>GetMessage("KEY_ACTIONS"),
		"LINK"=>"",
		"MENU" => $arSubMenu,
		"ICON"=>"btn_new",
	);

}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($request->get('mess') == "ok" && $ID > 0)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("KEY_SAVED"), "TYPE"=>"OK"));

if($message)
	echo $message->Show();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
	<?echo bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<?if($ID > 0 && !$bCopy):?>
		<input type="hidden" name="ID" value="<?=$ID?>">
	<?endif;?>
	<?if ($bCopy):?>
		<input type="hidden" name="copyID" value="<?= intval($ID); ?>">
	<?endif?>

	<?$tabControl->Begin();?>
	<?$tabControl->BeginNextTab();?>

	<tr>
		<td width="40%">
			<span id="hint_ACTIVE"></span><script type="text/javascript">BX.hint_replace(BX('hint_ACTIVE'), '<?=getMessage('ACTIVE_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["ACTIVE"]['title']?>:
		</td>
		<td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($arKey["ACTIVE"] == "Y") echo " checked"?> /></td>
	</tr>
	<tr>
		<td>
			<span id="hint_ACTIVE_FROM"></span><script type="text/javascript">BX.hint_replace(BX('hint_ACTIVE_FROM'), '<?=getMessage('ACTIVE_FROM_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["ACTIVE_FROM"]['title']?>:
		</td>
		<td><?= CAdminCalendar::CalendarDate("ACTIVE_FROM", $arKey["ACTIVE_FROM"], 19, true)?></td>
	</tr>
	<tr>
		<td>
			<span id="hint_ACTIVE_TO"></span><script type="text/javascript">BX.hint_replace(BX('hint_ACTIVE_TO'), '<?=getMessage('ACTIVE_TO_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["ACTIVE_TO"]['title']?>:
		</td>
		<td><?= CAdminCalendar::CalendarDate("ACTIVE_TO", $arKey["ACTIVE_TO"], 19, true)?></td>
	</tr>
	<tr>
		<td>
			<span id="hint_SORT"></span><script type="text/javascript">BX.hint_replace(BX('hint_SORT'), '<?=getMessage('SORT_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["SORT"]['title']?>:
		</td>
		<td><input type="text" name="SORT" value="<?=htmlspecialcharsbx($arKey["SORT"]);?>" size="1"></td>
	</tr>
	<tr>
		<td>
			<span id="hint_CODE"></span><script type="text/javascript">BX.hint_replace(BX('hint_CODE'), '<?=getMessage('CODE_FIELD_HINT')?>');</script>
			<span class="required"> * </span><b><?=$dataKeysMap["CODE"]['title']?></b>:
		</td>
		<td><input type="text" name="CODE" value="<?=htmlspecialcharsbx($arKey["CODE"]);?>" size="30"></td>
	</tr>
	<tr>
		<td>
			<span id="hint_NAME"></span><script type="text/javascript">BX.hint_replace(BX('hint_NAME'), '<?=getMessage('NAME_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["NAME"]['title']?>:
		</td>
		<td><input type="text" name="NAME" value="<?=htmlspecialcharsbx($arKey["NAME"]);?>" size="30"></td>
	</tr>
	<tr>
		<td>
			<span id="hint_VALUE"></span><script type="text/javascript">BX.hint_replace(BX('hint_VALUE'), '<?=getMessage('VALUE_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["VALUE"]['title']?>:
		</td>
		<td><input type="text" name="VALUE" value="<?=htmlspecialcharsbx($arKey["VALUE"]);?>" size="30"></td>
	</tr>

	<?$tabControl->BeginNextTab();?>

	<tr valign="top">
		<td>
			<span id="hint_SHOW_ON"></span><script type="text/javascript">BX.hint_replace(BX('hint_SHOW_ON'), '<?=getMessage('SHOW_ON_HINT')?>');</script>
			<?=getMessage("SHOW_ON_FIELD")?>:
		</td>
		<td>
			<textarea name="SHOW_ON" id="SHOW_ON" cols="45" rows="6" wrap="OFF"><?=htmlspecialcharsbx($arKey["SHOW_ON"]);?></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td>
			<span id="hint_SHOW_ON"></span><script type="text/javascript">BX.hint_replace(BX('hint_SHOW_ON'), '<?=getMessage('SHOW_ON_HINT')?>');</script>
			<?=getMessage("SHOW_OFF_FIELD")?>:
		</td>
		<td>
			<textarea name="SHOW_OFF" id="SHOW_OFF" cols="45" rows="6"><?=htmlspecialcharsbx($arKey["SHOW_OFF"]);?></textarea>
		</td>
	</tr>
	<?if ($siteList):?>
		<tr valign="top">
			<td width="40%">
				<span id="hint_C_SITE"></span><script type="text/javascript">BX.hint_replace(BX('hint_C_SITE'), '<?=getMessage('C_SITE_FIELD_HINT')?>');</script>
				<?=$dataKeysMap["C_SITE"]['title']?>:
			</td>
			<td width="60%">
				<select class="typeselect" multiple="" name="arrSITE[]" id="arrSITE[]" size="5">
					<?foreach($siteList as $site):?>
						<option value="<?=$site["LID"]?>" <?=(in_array($site["LID"],$arKey["arrSITE"]) ? "selected" : "" )?>><?=$site["LID"]?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
	<?endif;?>
	<?if ($siteTemplateList):?>
		<tr valign="top">
			<td>
				<span id="hint_C_TEMPLATE"></span><script type="text/javascript">BX.hint_replace(BX('hint_C_TEMPLATE'), '<?=getMessage('C_TEMPLATE_FIELD_HINT')?>');</script>
				<?=$dataKeysMap["C_TEMPLATE"]['title']?>:
			</td>
			<td>
				<select class="typeselect" multiple="" name="arrTEMPLATE[]" id="arrTEMPLATE[]" size="5">
					<?foreach($siteTemplateList as $template):?>
						<option value="<?=$template["ID"]?>" <?=(in_array($template["ID"],$arKey["arrTEMPLATE"]) ? "selected" : "" )?>><?=$template["ID"]?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
	<?endif;?>
	<tr>
		<td>
			<span id="hint_C_REQUEST"></span><script type="text/javascript">BX.hint_replace(BX('hint_C_REQUEST'), '<?=getMessage('C_REQUEST_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["C_REQUEST"]['title']?>:
		</td>
		<td><input type="text" name="C_REQUEST" value="<?=htmlspecialcharsbx($arKey["C_REQUEST"]);?>" size="30"></td>
	</tr>
	<tr>
		<td>
			<span id="hint_C_PHP"></span><script type="text/javascript">BX.hint_replace(BX('hint_C_PHP'), '<?=getMessage('C_PHP_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["C_PHP"]['title']?>:
		</td>
		<td><input type="text" name="C_PHP" value="<?=htmlspecialcharsbx($arKey["C_PHP"]);?>" size="60"></td>
	</tr>
	<tr>
		<td>
			<input type="hidden" name="back_url" value="<?=$request->get("back_url");?>">
		</td>
	</tr>

<?
$tabControl->Buttons(
	array(
		"disabled"=> false,
		"back_url"=>Shantilab\Metatags\Data::getPageName('edit_key')."?lang=".LANG,
	)
);
?>

<?$tabControl->End();?>
<?$tabControl->ShowWarnings("post_form", $message);?>

<?echo BeginNote();?>
	<span class="required">*</span> <?= GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>