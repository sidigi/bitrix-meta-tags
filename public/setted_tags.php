<?define('BX_PUBLIC_MODE', 0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

$moduleMode = CModule::IncludeModuleEx('shantilab.metatags');
IncludeModuleLangFile(__FILE__);

if (!$moduleMode){
	echo GetMessage("MODULE_IS_NOT_INSTALL");
	return;
}

global $APPLICATION;

$TAGS_RIGHT = $APPLICATION->GetGroupRight('shantilab.metatags');
if ($TAGS_RIGHT < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$obJSPopup = new CJSPopup("lang=".urlencode($_GET["lang"])."&site=".urlencode($_GET["site"])."&back_url=".urlencode($_GET["back_url"]));
$obJSPopup->ShowTitlebar(getMessage("SETTED_TAGS_TITLE"));
$tagsInfo = unserialize(file_get_contents($_SERVER["DOCUMENT_ROOT"].'/upload/shantilab.metatags/pageinfo.txt'));

if ($moduleMode == 2){
	echo BeginNote();
	echo GetMessage("DEMO_INFO");
	echo EndNote();
}
$path = htmlspecialcharsbx($_GET["path"]);
?>
	<span><?=getMessage("SETTED_TAGS_TITLE_2")?> <b><?=$path?></b></span>
	<br/>
<?if ($tagsInfo["RULE"]):?>
	<?$ruleLink ="<a style=\"display: block; margin: -13px 0 15px 0;\" href=\"#\" onclick=\"(new BX.CAdminDialog({'content_url':'/bitrix/admin/".Shantilab\Metatags\Data::getPageName("edit_rule")."?ID=".$tagsInfo["RULE"]["ID"]."lang=ru&bxpublic=Y&site=".SITE_ID."','width':'700','height':'400'})).Show()\">".getMessage("SETTED_RULE_CHANGE")."</a>"?>
	<?=CAdminMessage::ShowNote(getMessage('RULE_FOUND').' '.$tagsInfo["RULE"]["NAME"]);?>
	<?=$ruleLink?>
<?else:?>
	<?$message = new CAdminMessage(getMessage('NO_RULE_FOUND'))?>
	<?=$message->Show()?>
<?endif;?>
	<table class="adm-detail-content-table edit-table" style="width: 100%;">
		<?if ($tagsInfo["TAGS"]["SETTED"]):?>
			<tr class="heading">
				<td colspan="2"><?=getMessage('SETTED_TAGS_NAME')?></td>
			</tr>
			<?foreach($tagsInfo["TAGS"]["SETTED"] as $tag):?>
				<tr>
					<td width="10%" class="adm-detail-content-cell-l">
						<?=getMessage('SETTED_TAGS_TAG')?>:
					</td>
					<td width="90%" class="adm-detail-content-cell-r">
						<b><?=(toLower($tag["CODE"]))?></b>
					</td>
				</tr>
				<?if ($tag["TYPE"] == "MODULE"):?>
					<tr>
						<td width="10%" class="adm-detail-content-cell-l">
							<?=getMessage('SETTED_TAGS_MASK')?>:
						</td>
						<td width="90%" class="adm-detail-content-cell-r">
							<?=$tag["MASK"]?>
						</td>
					</tr>
				<?endif;?>
				<tr>
					<td width="10%" class="adm-detail-content-cell-l">
						<?=getMessage('SETTED_TAGS_VALUE')?>:
					</td>
					<td width="90%" class="adm-detail-content-cell-r">
						<?=$tag["VALUE"]?>
					</td>
				</tr>
				<tr>
					<td width="10%" class="adm-detail-content-cell-l">
						<?=getMessage('SETTED_TAGS_TYPE')?>:
					</td>
					<td width="90%" class="adm-detail-content-cell-r">
						<b style="color: green;"><?=($tag["TYPE"] == "MODULE") ? getMessage('SETTED_TAGS_MODULE') : getMessage('SETTED_TAGS_BITRIX')?></b>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<hr/>
					</td>
				</tr>
			<?endforeach;?>
		<?endif;?>
		<?if ($tagsInfo["TAGS"]["BITRIX"]):?>
			<tr class="heading">
				<td colspan="2"><?=getMessage('SETTED_TAGS_SETTED_BITRIX')?></td>
			</tr>
			<?foreach($tagsInfo["TAGS"]["BITRIX"] as $tag):?>
				<tr>
					<td width="10%" class="adm-detail-content-cell-l">
						<?=getMessage('SETTED_TAGS_TAG')?>:
					</td>
					<td width="90%" class="adm-detail-content-cell-r">
						<b><?=(toLower($tag["CODE"]))?></b>
					</td>
				</tr>
				<tr>
					<td width="10%" class="adm-detail-content-cell-l">
						<?=getMessage('SETTED_TAGS_VALUE')?>:
					</td>
					<td width="90%" class="adm-detail-content-cell-r">
						<?=$tag["VALUE"]?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<hr/>
					</td>
				</tr>
			<?endforeach;?>
		<?endif;?>
		<?if ($tagsInfo["RULE"]["TAGS"]):?>
			<tr class="heading">
				<td colspan="2"><?=getMessage('SETTED_TAGS_SETTED_MODULE')?></td>
			</tr>
			<?foreach($tagsInfo["RULE"]["TAGS"] as $tag):?>
				<tr>
					<td width="10%" class="adm-detail-content-cell-l">
						<?=getMessage('SETTED_TAGS_TAG')?>:
					</td>
					<td width="90%" class="adm-detail-content-cell-r">
						<b><?=(toLower($tag["CODE"]))?></b>
					</td>
				</tr>
				<tr>
					<td width="10%" class="adm-detail-content-cell-l">
						<?=getMessage('SETTED_TAGS_MASK')?>:
					</td>
					<td width="90%" class="adm-detail-content-cell-r">
						<?=$tag["VALUE"]?>
					</td>
				</tr>
				<tr>
					<td width="10%" class="adm-detail-content-cell-l">
						<?=getMessage('SETTED_TAGS_VALUE')?>:
					</td>
					<td width="90%" class="adm-detail-content-cell-r">
						<?=$tag["FINAL_VALUE"]?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<hr/>
					</td>
				</tr>
			<?endforeach;?>
		<?endif;?>
	</table>
<?
$obJSPopup->StartButtons();
$obJSPopup->ShowStandardButtons(array('close'));
$obJSPopup->EndButtons();
?>