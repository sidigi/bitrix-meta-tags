<?
$module_id = "shantilab.metatags";
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$TAGS_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($TAGS_RIGHT>="R") :

if ($REQUEST_METHOD=="GET" && $TAGS_RIGHT=="W" && strlen($RestoreDefaults)>0 && check_bitrix_sessid())
{
	COption::RemoveOption($module_id);
	$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($zr = $z->Fetch())
		$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
}

$arAllOptions = array(
	array("MODULE_ON", GetMessage("MODULE_ON"), Array("checkbox", "Y"), "HINT"),
	array("PRIORITY", GetMessage("PRIORITY"), Array("select", array("MODULE","BITRIX")), "HINT"),
	array("TITLE_1", GetMessage("TITLE_1"), Array("checkbox", "Y"), "HINT"),
	array("TITLE_2", GetMessage("TITLE_2"), Array("checkbox", "Y"), "HINT"),
);
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "ad_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && $TAGS_RIGHT=="W" && strlen($Update.$Apply)>0 && check_bitrix_sessid())
{

	for($i=0; $i<count($arAllOptions); $i++)
	{
		$name = $arAllOptions[$i][0];
		$val = $$name;
		if($arAllOptions[$i][2][0]=="checkbox" && $val!="Y") $val="N";
		COption::SetOptionString($module_id, $name, $val);
	}

	$Update = $Update.$Apply;

	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
	ob_end_clean();

	if($Apply == '' && $_REQUEST["back_url_settings"] <> '')
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}
$tabControl->Begin();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>">
<?
$tabControl->BeginNextTab();
?>
	<?
	for($i = 0; $i < count($arAllOptions); $i++):
		$Option = $arAllOptions[$i];
		$val = COption::GetOptionString($module_id, $Option[0]);
		$type = $Option[2];
	?>
		<tr>
			<td width="50%">
				<?if ($Option[3] == 'HINT'):?>
					<span id="hint_"<?=$Option[0]?>></span><script type="text/javascript">BX.hint_replace(BX('hint_'<?=$Option[0]?>), '<?=getMessage($Option[0].'_HINT')?>');</script>
				<?endif;?>
				<?if($type[0]=="checkbox")
					echo "<label for=\"".htmlspecialcharsbx($Option[0])."\">".$Option[1]."</label>";
				else
					echo $Option[1];?>
			</td>
			<td width="50%"><?
					if($type[0]=="checkbox"):?>
						<input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val=="Y")echo " checked";?>>
					<?
					elseif($type[0]=="text"):
					?>
						<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?
						if (strlen($Option[3])>0) :
						?>&nbsp;<label for="<?echo htmlspecialcharsbx($Option[0])?>_clear"><?=GetMessage("AD_CLEAR")?>:</label><input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>_clear" id="<?echo htmlspecialcharsbx($Option[0])?>_clear" value="Y"><?
						endif;
						?><?
						if (strlen($Option[4])>0) :
						?>&nbsp;&nbsp;(<?echo GetMessage("AD_RECORDS")?>&nbsp;<?echo $count?>)<?
						endif;
						?>
					<?elseif($type[0]=="textarea"):?>
						<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
					<?elseif($type[0]=="select"):?>
						<select name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>">
							<?foreach($Option[2][1] as $opt):?>
								<option value="<?echo htmlspecialcharsbx($opt)?>" <?=($val == $opt) ? "selected" : ""?>><?=getMessage("OPTION_".$opt)?></option>
							<?endforeach;?>
						</select>
					<?endif?>
			</td>
		</tr>
	<?
	endfor;
	?>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
<script type="text/javascript">
function RestoreDefaults()
{
	if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANGUAGE_ID?>&mid=<?echo urlencode($mid)?>&<?echo bitrix_sessid_get()?>";
}
</script>
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" <?if ($TAGS_RIGHT < "W") echo " disabled" ?>>
	<?endif?>
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>" class="adm-btn-save" <?if ($TAGS_RIGHT < "W") echo " disabled" ?>>
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::JSEscape($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>" <?if ($TAGS_RIGHT < "W") echo " disabled" ?>>
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
<?endif;?>