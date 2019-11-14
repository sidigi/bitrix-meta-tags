<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shantilab.metatags/prolog.php");
$moduleMode = \CModule::IncludeModuleEx(ADMIN_MODULE_NAME);
CJSCore::Init(array("jquery"));
IncludeModuleLangFile(__FILE__);

if (!$moduleMode){
	echo GetMessage("MODULE_IS_NOT_INSTALL");
	return;
}

$TAGS_RIGHT = $APPLICATION->GetGroupRight(ADMIN_MODULE_NAME);
if ($TAGS_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

global $DB;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => getMessage("RULE_TAB_NAME"), "ICON"=>"main_user_edit", "TITLE"=>getMessage("RULE_TAB_NAME")),
	array("DIV" => "edit2", "TAB" => getMessage("RULE_TAGS_TAB_NAME"), "ICON"=>"main_user_edit", "TITLE" => getMessage("RULE_TAGS_TAB_TITLE")),
	array("DIV" => "edit3", "TAB" => getMessage("RULE_CONDITIONS_TAB_NAME"), "ICON"=>"main_user_edit", "TITLE" => getMessage("RULE_CONDITIONS_TAB_TITLE")),
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
	$res = Shantilab\MetaTags\Rule\DB::delete($ID);

	if ($res->isSuccess()){
		LocalRedirect("/bitrix/admin/".Shantilab\Metatags\Data::getPageName('list_rule')."?lang=".LANG);
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
		"C_ALL_KEYS"     => $C_ALL_KEYS,
		"C_REQUIRED_KEYS"  => $C_REQUIRED_KEYS,
		"SORT"           => $SORT,
		"NAME"           => $NAME,
		"C_SITE"         => $arrSITE,
		"C_TEMPLATE"     => $arrTEMPLATE,
		"C_REQUEST"      => $C_REQUEST,
		"C_PHP"          => $C_PHP,
		"SHOW_ON"        => $SHOW_ON,
		"SHOW_OFF"       => $SHOW_OFF,
		"TEMPLATES"      => $arrTAGTEMPALTE,
		"PRIORITY"       => $PRIORITY
	);

	if($ID > 0)
	{
		if ($bCopy){
			$res = Shantilab\MetaTags\Rule\DB::add($arFields);
			if ($res->isSuccess()){
				$ID = $res->getId();
			}
		}else{
			$res = Shantilab\MetaTags\Rule\DB::update($ID,$arFields);
		}

		if ($res->isSuccess()){
			$success = true;
		}else{
			$message = new CAdminMessage(implode("<br>", $res->getErrorMessages()));
		}
	}
	else
	{
		$res = Shantilab\MetaTags\Rule\DB::Add($arFields);
		if ($res->isSuccess()){
			$ID = $res->getId();
			$success = true;
		}else{
			$message = new CAdminMessage(implode("<br>", $res->getErrorMessages()));
		}
	}

	if($success)
	{
		if ($apply != "")
			LocalRedirect("/bitrix/admin/".Shantilab\Metatags\Data::getPageName('edit_rule')."?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("/bitrix/admin/".Shantilab\Metatags\Data::getPageName('list_rule')."?lang=".LANG);
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("RULE_ERROR_SAVE"), $e);
		$bVarsFromForm = true;
	}
}

$dataKeysMap = Shantilab\MetaTags\Rule\RulesTable::getMap();
$siteList = Shantilab\MetaTags\Data::getSiteList();
$siteTemplateList = Shantilab\MetaTags\Data::getTemplateList();

$arRule = array();
$arRule["ACTIVE"]        = "Y";
$arRule["SORT"]          = 500;

if($ID > 0)
{
	$res = Shantilab\MetaTags\Rule\DB::getById($ID);

	if($arRule = $res->fetch()){
		if ($arRule["C_SITE"]){
			$arRule["arrSITE"] = array_filter(explode("#",$arRule["C_SITE"]));
		}
		if ($arRule["C_TEMPLATE"]){
			$arRule["arrTEMPLATE"] = array_filter(explode("#",$arRule["C_TEMPLATE"]));
		}

		$res = Shantilab\MetaTags\Rule\DB::getPageList($ID);
		$arRule["SHOW_ON"] = '';
		$arRule["SHOW_OFF"] = '';
		while($arTmp = $res->fetch()){
			if ($arTmp["SHOW_ON_PAGE"] == "Y"){
				$arRule["SHOW_ON"] .= $arTmp["PAGE"]."\r\n";
			}else{
				$arRule["SHOW_OFF"] .= $arTmp["PAGE"]."\r\n";
			}
		}
		$res = Shantilab\MetaTags\Rule\DB::getTemplateList($ID);
		$arRule["arrTAGTEMPALTE"] = array();
		while($arTmp = $res->fetch()){
			$arRule["arrTAGTEMPALTE"][] = $arTmp;
		}
	}else{
		$ID = 0;
	}
}

if($bVarsFromForm){
	$arRule = $request->toArray();
}


if (!is_array($arRule["arrSITE"])){
	$arRule["arrSITE"] = array();
}

if (!is_array($arRule["arrSITE"])){
	$arRule["arrTEMPLATE"] = array();
}

$APPLICATION->SetTitle(( $ID > 0 ? GetMessage("RULE_TITLE_EDIT").$ID : GetMessage("RULE_TITLE_ADD")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($moduleMode == 2){
	echo BeginNote();
	echo GetMessage("DEMO_INFO");
	echo EndNote();
}

$aMenu = array();
$aMenu[] = array(
	"TEXT"  => GetMessage("RULES_TITLE"),
	"TITLE" => GetMessage("RULES_TITLE"),
	"LINK"  => Shantilab\Metatags\Data::getPageName('list_rule')."?lang=".LANG,
	"ICON"  => "btn_list",
);

if($ID > 0){
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"=>GetMessage("COPY_RULE"),
		"TITLE"=>GetMessage("COPY_RULE"),
		"LINK"=> Shantilab\Metatags\Data::getPageName('edit_rule')."?ID=".$ID."&action=copy&lang=".LANG,
		"ICON"=>"btn_copy",
	);

	$arSubMenu = array();
	$arSubMenu[] = array(
		"TEXT"  => GetMessage("RULE_ADD"),
		"TITLE" => GetMessage("RULE_ADD"),
		"LINK"  => Shantilab\Metatags\Data::getPageName('edit_rule')."?lang=".LANG,
		"ICON"  => "new",
	);
	$arSubMenu[] = array(
		"TEXT"  => GetMessage("RULE_DEL"),
		"TITLE" => GetMessage("RULE_DEL"),
		"LINK"  => "javascript:if(confirm('".GetMessage("RULE_DEL_CONFIRM")."')) ".
			"window.location='".Shantilab\Metatags\Data::getPageName('edit_rule')."?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"  => "delete",
	);

	$aMenu[] = array(
		"TEXT"=>GetMessage("RULE_ACTIONS"),
		"TITLE"=>GetMessage("RULE_ACTIONS"),
		"LINK"=>"",
		"MENU" => $arSubMenu,
		"ICON"=>"btn_new",
	);

}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($request->get('mess') == "ok" && $ID > 0)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("RULE_SAVED"), "TYPE"=>"OK"));

if($message)
	echo $message->Show();
?>
<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
<?echo bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID > 0 && !$bCopy):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?if ($bCopy):?>
	<input type="hidden" name="copyID" value="<?= intval($ID); ?>">
<?endif?>

<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%">
			<span id="hint_ACTIVE"></span><script type="text/javascript">BX.hint_replace(BX('hint_ACTIVE'), '<?=getMessage('ACTIVE_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["ACTIVE"]['title']?>:
		</td>
		<td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($arRule["ACTIVE"] == "Y") echo " checked"?> /></td>
	</tr>
	<tr>
		<td>
			<span id="hint_ACTIVE_FROM"></span><script type="text/javascript">BX.hint_replace(BX('hint_ACTIVE_FROM'), '<?=getMessage('ACTIVE_FROM_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["ACTIVE_FROM"]['title']?>:
		</td>
		<td><?= CAdminCalendar::CalendarDate("ACTIVE_FROM", $arRule["ACTIVE_FROM"], 19, true)?></td>
	</tr>
	<tr>
		<td>
			<span id="hint_ACTIVE_TO"></span><script type="text/javascript">BX.hint_replace(BX('hint_ACTIVE_TO'), '<?=getMessage('ACTIVE_TO_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["ACTIVE_TO"]['title']?>:
		</td>
		<td><?= CAdminCalendar::CalendarDate("ACTIVE_TO", $arRule["ACTIVE_TO"], 19, true)?></td>
	</tr>
	<tr>
		<td>
			<span id="hint_NAME"></span><script type="text/javascript">BX.hint_replace(BX('hint_NAME'), '<?=getMessage('NAME_FIELD_HINT')?>');</script>
			<b><?=$dataKeysMap["NAME"]['title']?></b><span class="required">*</span>:
		</td>
		<td><input type="text" name="NAME" value="<?=htmlspecialcharsbx($arRule["NAME"]);?>" size="30"></td>
	</tr>
	<tr>
		<td>
			<span id="hint_SORT"></span><script type="text/javascript">BX.hint_replace(BX('hint_SORT'), '<?=getMessage('SORT_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["SORT"]['title']?>:
		</td>
		<td><input type="text" name="SORT" value="<?=htmlspecialcharsbx($arRule["SORT"]);?>" size="1"></td>
	</tr>
	<tr>
		<td>
			<span id="hint_PRIORITY"></span><script type="text/javascript">BX.hint_replace(BX('hint_PRIORITY'), '<?=getMessage('PRIORITY_HINT')?>');</script>
			<?=$dataKeysMap["PRIORITY"]['title']?>:
		</td>
		<td>
			<select name="PRIORITY" id="PRIORITY">
				<option value=""><?=getMessage('MODULE_OPTIONS_TITLE')?></option>
				<option value="Y" <?=($arRule["PRIORITY"] == "Y") ? 'selected' : ''?>><?=getMessage('MODULE_MODULE_TITLE')?></option>
				<option value="N" <?=($arRule["PRIORITY"] == "N") ? 'selected' : ''?>><?=getMessage('MODULE_BITRIX_TITLE')?></option>
			</select>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<?
	array_filter($arRule["arrTAGTEMPALTE"]);
	if ($arRule["arrTAGTEMPALTE"]):?>
		<?foreach($arRule["arrTAGTEMPALTE"] as $key => $template):?>
			<tr class="heading">
				<td colspan="2" style="padding-right:0 !important;"><?=getMessage("TEMPLATE_FIELD")?> <b>[<?=$template["CODE"]?>]</b>: <a href="#" class="tag-delete" style="float: right; overflow: hidden;" title="<?=getMessage("DELETE_TAG")?>"><span class="bx-core-popup-menu-item-icon adm-menu-delete" style="margin-top: -5px;height: 21px;position: static;"></span></a></td>
			</tr>
			<tr valign="top">
				<td width="0%">
					<nobr><span id="hint_TEMPLATE"></span><script type="text/javascript">BX.hint_replace(BX('hint_TEMPLATE'), '<?=getMessage('TEMPLATE_FIELD_HINT')?>');</script>
					<?=getMessage("TEMPLATE_FIELD")?>:</nobr>
				</td>
				<td width="100%" data-index="<?=$key?>">
					<b><?=getMessage("TEMPLATE_CODE_FIELD")?></b><span class="required">*</span>:
					<br/>
					<input type="text" name="arrTAGTEMPALTE[<?=$key?>][CODE]" value="<?=htmlspecialcharsbx($template["CODE"])?>" size="43">
					<br/>
					<div style="margin-top: 5px;"></div>
					<?=getMessage("TEMPLATE_VAL_FIELD")?><span class="required">*</span>:
					<br/>
					<textarea name="arrTAGTEMPALTE[<?=$key?>][VALUE]" style="width: 95%" rows="10" wrap="OFF"><?=htmlspecialcharsbx($template["VALUE"])?></textarea>
					<br/>
					<input type="checkbox" name="arrTAGTEMPALTE[<?=$key?>][PAGEN_ON]" value="Y" class="pagen_use" <?=($template["PAGEN_ON"] == 'Y') ? "checked" : "N"?>/>
					<?=getMessage("PAGEN_CHECK_FIELD")?>:
					<br/>
					<div style="margin-top: 5px;"></div>
					<input type="text" name="arrTAGTEMPALTE[<?=$key?>][PAGEN]" value="<?=htmlspecialcharsbx($template["PAGEN"])?>" size="100" class="pagen_text" />
					<br/>
					<br/>
				</td>
			</tr>
		<?endforeach;?>
	<?else:?>
		<tr class="heading">
			<td colspan="2"><?=getMessage("TEMPLATE_FIELD")?>:</td>
		</tr>
		<tr valign="top">
			<td width="0%">
				<nobr><span id="hint_TEMPLATE"></span><script type="text/javascript">BX.hint_replace(BX('hint_TEMPLATE'), '<?=getMessage('TEMPLATE_FIELD_HINT')?>');</script>
				<?=getMessage("TEMPLATE_FIELD")?>:</nobr>
			</td>
			<td width="100%" data-index="0">
				<b><?=getMessage("TEMPLATE_CODE_FIELD")?></b><span class="required">*</span>:
				<br/>
				<input type="text" name="arrTAGTEMPALTE[0][CODE]" value="" size="43">
				<br/>
				<div style="margin-top: 5px;"></div>
				<?=getMessage("TEMPLATE_VAL_FIELD")?><span class="required">*</span>:
				<br/>
				<textarea name="arrTAGTEMPALTE[0][VALUE]" style="width: 95%" rows="10" wrap="OFF"></textarea>
				<br/>
				<input type="checkbox" name="arrTAGTEMPALTE[0][PAGEN_ON]" value="Y" class="pagen_use"/>
				<?=getMessage("PAGEN_CHECK_FIELD")?>:
				<br/>
				<div style="margin-top: 5px;"></div>
				<input type="text" name="arrTAGTEMPALTE[0][PAGEN]" value="" size="100" class="pagen_text" />
				<br/>
				<br/>
			</td>
		</tr>
	<?endif;?>
	<tr>
		<td>
		</td>
		<td>
			<input type="button" value="<?=getMessage('MORE')?>" class="add_more"/>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=getMessage("ALL_SETTED_KEYS")?>:</td>
	</tr>
	<tr>
		<td colspan="2">
			<?if (!$arRule["SETTED_KEYS"]):?>
				<div style="text-align: center; color: #6D6D6D; font-weight: bold;"><?=getMessage("NO_KEYS_FOUND")?></div>
			<?else:?>
				<div style="text-align: center; color: #6D6D6D; font-weight: bold;">
					<?$settedKeys = explode(',',$arRule["SETTED_KEYS"])?>
					<?foreach($settedKeys as $key):?>
						<span style="padding-right: 10px;">[<?=$key?>]</span>
					<?endforeach;?>
				</div>
			<?endif;?>
			<input type="hidden" name="SETTED_KEYS" value="<?=$arRule["SETTED_KEYS"]?>"/>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr valign="top">
		<td>
			<span id="hint_SHOW_ON"></span><script type="text/javascript">BX.hint_replace(BX('hint_SHOW_ON'), '<?=getMessage('SHOW_ON_HINT')?>');</script>
			<?=getMessage("SHOW_ON_FIELD")?>:
		</td>
		<td>
			<textarea name="SHOW_ON" id="SHOW_ON" cols="45" rows="6" wrap="OFF"><?=htmlspecialcharsbx($arRule["SHOW_ON"]);?></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td>
			<span id="hint_SHOW_ON"></span><script type="text/javascript">BX.hint_replace(BX('hint_SHOW_ON'), '<?=getMessage('SHOW_ON_HINT')?>');</script>
			<?=getMessage("SHOW_OFF_FIELD")?>:
		</td>
		<td>
			<textarea name="SHOW_OFF" id="SHOW_OFF" cols="45" rows="6"><?=htmlspecialcharsbx($arRule["SHOW_OFF"]);?></textarea>
		</td>
	</tr>
	<tr valign="top">
		<td width="40%">
			<span id="hint_C_REQUIRED_KEYS"></span><script type="text/javascript">BX.hint_replace(BX('hint_C_REQUIRED_KEYS'), '<?=getMessage('C_REQUIRED_KEYS_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["C_REQUIRED_KEYS"]['title']?>:
		</td>
		<td width="60%">
			<select class="typeselect" name="C_ALL_KEYS" id="C_ALL_KEYS">
				<option value="" <?=(!$arRule["C_ALL_KEYS"]) ? "selected" : ""?>><?=getMessage("NO_CHOICE")?></option>
				<option value="Y" <?=($arRule["C_ALL_KEYS"] == "Y") ? "selected" : ""?>><?=getMessage("ALL_KEYS_REQUIRED")?></option>
				<option value="N" class="last-keys"<?=($arRule["C_ALL_KEYS"] == "N" && $arRule["C_REQUIRED_KEYS"]) ? "selected" : ""?>><?=getMessage("KEYS_FROM_LIST_REQUIRED")?></option>
			</select>
			<br/>
			<div style="margin-top: 5px;" id="C_REQUIRED_KEYS">
				<input type="text" name="C_REQUIRED_KEYS" value="<?=$arRule["C_REQUIRED_KEYS"]?>" size="100">
				<span id="hint_LIST_KEYS"></span><script type="text/javascript">BX.hint_replace(BX('hint_LIST_KEYS'), '<?=getMessage('LIST_KEYS_FIELD_HINT')?>');</script>
				<br/><input type="button" value="<?=getMessage('PASTE_KEYS')?>" class="add_keys">
			</div>
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
					<option value="<?=$site["LID"]?>" <?=(in_array($site["LID"],$arRule["arrSITE"]) ? "selected" : "" )?>><?=$site["LID"]?></option>
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
					<option value="<?=$template["ID"]?>" <?=(in_array($template["ID"],$arRule["arrTEMPLATE"]) ? "selected" : "" )?>><?=$template["ID"]?></option>
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
		<td><input type="text" name="C_REQUEST" value="<?=htmlspecialcharsbx($arRule["C_REQUEST"]);?>" size="30"></td>
	</tr>
	<tr>
		<td>
			<span id="hint_C_PHP"></span><script type="text/javascript">BX.hint_replace(BX('hint_C_PHP'), '<?=getMessage('C_PHP_FIELD_HINT')?>');</script>
			<?=$dataKeysMap["C_PHP"]['title']?>:
		</td>
		<td><input type="text" name="C_PHP" value="<?=htmlspecialcharsbx($arRule["C_PHP"]);?>" size="60"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=getMessage("ALL_SETTED_KEYS")?>:</td>
	</tr>
	<tr>
		<td colspan="2">
			<?if (!$arRule["SETTED_KEYS"]):?>
				<div style="text-align: center; color: #6D6D6D; font-weight: bold;"><?=getMessage("NO_KEYS_FOUND")?></div>
			<?else:?>
				<div style="text-align: center; color: #6D6D6D; font-weight: bold;" class="keys-setted">
					<?=$arRule["SETTED_KEYS"]?>
				</div>
			<?endif;?>
		</td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=> false,
		"back_url"=>Shantilab\Metatags\Data::getPageName('edit_rule')."?lang=".LANG,
	)
);
?>
<?$tabControl->End();?>

<?$tabControl->ShowWarnings("post_form", $message);?>

<?echo BeginNote();?>
	<span class="required">*</span> <?= GetMessage("REQUIRED_FIELDS")?>
<?echo EndNote();?>

<script>

	$(document).ready(function(){
		$(document).on('click','.add_keys',function(){
			var _this = $(this);
			$('input[name=C_REQUIRED_KEYS]').val($.trim($('.keys-setted').text()));
		});

		$(document).on('change',"#C_ALL_KEYS",function(){
			var _this = $(this);

			if ($("#C_ALL_KEYS :selected").hasClass('last-keys')){
				$("#C_REQUIRED_KEYS").show();
			}else{
				$("#C_REQUIRED_KEYS").hide();
			}
		});

		$(document).on('click','.add_more',function(event){
			var _this = $(this),
				trData = _this.closest('tr').prev();

			var trHead = trData.prev().clone();
			var trCopy = trData.clone(true);
			var index = trCopy.find('td[data-index]').data("index");

			trHead.find('b').remove();
			trHead.find('a').remove();

			trCopy.find('input').val('');
			trCopy.find('.pagen_use').attr('id',"pagen_" + parseInt(index+1));
			trCopy.find('label').attr("for","pagen_" + parseInt(index+1)).attr('id','label_' + parseInt(index+1));
			trCopy.find('textarea').text('');
			trCopy.find('input').prop('checked',false);
			trCopy.find('td[data-index]').attr("data-index",parseInt(index) + 1);


			trCopy.find('input, textarea').each(function(){
				var _this = $(this);
				_this.attr('name',_this.attr('name').replace('['+index+']','['+(index+1)+']'))
			});

			trData.after(trCopy);
			trData.after(trHead);
			event.preventDefault();
		});

		$(document).on('click','.tag-delete',function(event){
			var _this = $(this);
			var trHead = _this.closest('tr');
			var trData = trHead.next();

			if ($('.pagen_use').length <= 2)
				$('.add_more').click();

			trHead.remove();
			trData.remove();

			event.preventDefault();
		});

		$('#C_ALL_KEYS').change();
	});
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>