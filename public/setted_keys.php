<?define('BX_PUBLIC_MODE', 0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$moduleMode = \CModule::IncludeModuleEx('shantilab.metatags');
IncludeModuleLangFile(__FILE__);
CJSCore::Init(array("jquery"));

if (!$moduleMode){
	echo GetMessage("MODULE_IS_NOT_INSTALL");
	return;
}

global $APPLICATION;

$TAGS_RIGHT = $APPLICATION->GetGroupRight('shantilab.metatags');
if ($TAGS_RIGHT < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$obJSPopup = new CJSPopup("lang=".urlencode($_GET["lang"])."&site=".urlencode($_GET["site"])."&back_url=".urlencode($_GET["back_url"]));
$obJSPopup->ShowTitlebar(getMessage("SETTED_KEYS_TITLE"));
$tagsInfo = unserialize(file_get_contents($_SERVER["DOCUMENT_ROOT"].'/upload/shantilab.metatags/pageinfo.txt'));

if ($moduleMode == 2){
	echo BeginNote();
	echo GetMessage("DEMO_INFO");
	echo EndNote();
}
$path = htmlspecialcharsbx($_GET["path"]);
?>

<div style="float: left;">
	<span><?=getMessage("SETTED_KEYS_TITLE_2")?> <b><?=$path?></b></span>
	<br/>
	<a href="#" class="mode"><?=getMessage("SETTED_KEYS_CUT")?></a>
	<br/>
	<br/>
</div>
<?if ($tagsInfo["KEYS"]):?>
	<div style="float: right;">
		<label for=""><?=getMessage("SEARCH")?> <input type="text" class="search-key"/></label>
	</div>
	<div style="clear:both;"></div>
	<table class="adm-detail-content-table edit-table" style="width: 100%;">
	<?foreach($tagsInfo["KEYS"] as $index => $key):?>
		<tr class="heading" data-key="<?=$index?>">
			<td colspan="2" class="key-name"><?=$key["NAME"]?> [<?=$key["CODE"]?>]</td>
		</tr>
		<tr data-key="<?=$index?>">
			<td width="50%" class="adm-detail-content-cell-l">
				<?=getMessage("SETTED_KEYS_NAME")?>:
			</td>
			<td width="50%" class="adm-detail-content-cell-r">
				<?=($key["NAME"]) ? $key["NAME"] : '-'?>
			</td>
		</tr>
		<tr data-key="<?=$index?>" class="mini-mode">
			<td class="adm-detail-content-cell-l">
				<?=getMessage("SETTED_KEYS_CODE")?>:
			</td>
			<td class="adm-detail-content-cell-r">
				<?=($key["CODE"]) ? $key["CODE"] : "-"?>
			</td>
		</tr>
		<tr data-key="<?=$index?>" class="mini-mode">
			<td class="adm-detail-content-cell-l">
				<?=getMessage("SETTED_KEYS_VALUE")?>:
			</td>
			<td class="adm-detail-content-cell-r">
				<?=($key["VALUE"]) ? $key["VALUE"] : '-'?>
			</td>
		</tr>
		<tr data-key="<?=$index?>">
			<td class="adm-detail-content-cell-l">
				<?=getMessage("SETTED_KEYS_ADD_VAL")?>:
			</td>
			<td class="adm-detail-content-cell-r">
				<?if ($key["SET_INFO"]["PATH"]):?>
					<?=getMessage("SETTED_KEYS_ON_PAGE")?>: <b><?=$key["SET_INFO"]["PATH"]?></b><br/>
				<?elseif ($key["SET_INFO"]["LINK"]):?>
					<?=getMessage("SETTED_KEYS_ON_DB")?>:
					<a href="#" onclick="(new BX.CAdminDialog({'content_url':'/bitrix/admin/<?=Shantilab\Metatags\Data::getPageName("edit_key")?>?ID=<?=$key["ID"]?>lang=ru&bxpublic=Y&site=<?=SITE_ID?>','width':'700','height':'400'})).Show()"><?=getMessage("SETTED_KEYS_CHANGE")?></a>
					<br/>
				<?else:?>
					-
				<?endif;?>
			</td>
		</tr>
		<?if ($key["DESCRIPTION"]):?>
			<tr data-key="<?=$index?>">
				<td class="adm-detail-content-cell-l">
					<?=getMessage("SETTED_KEYS_DESCRIPTION")?>:
				</td>
				<td class="adm-detail-content-cell-r">
					<?=$key["DESCRIPTION"]?>
				</td>
			</tr>
		<?endif;?>
	<?endforeach;?>
	</table>
<?else:?>
	<?
	$message = new CAdminMessage(getMessage('NO_KEYS_FOUND'))
	?>
	<table class="adm-detail-content-table edit-table" style="width: 100%;"><tr><td colspan="2"><?=$message->Show()?></td></tr></table>
<?endif;?>
<script>
	$(document).ready(function(){
		$(document).on('keyup','.search-key',function(){
			var _this = $(this);
			var keyName = _this.val();
			if ($('a.mode').hasClass('mini')){
				$('a.mode').click();
			}
			$('.key-name').each(function(){
				var _this = $(this);
					var index = _this.closest('tr').data('key');

				if (_this.text().toLowerCase().match(keyName.toLowerCase())){
					$("tr[data-key=" + index + "]").show();
				}else{
					$("tr[data-key=" + index + "]").hide();
				}
			});
		});

		$(document).on('click','.mode',function(event){
			var _this = $(this);

			_this.toggleClass('mini');
			var tdItems = $('.adm-detail-content-table td');
			var trItems = $('.adm-detail-content-table tr');

			if (_this.hasClass('mini')){
				trItems.hide();
				$('.adm-detail-content-table tr.mini-mode').show();
				tdItems.attr('width','');
				_this.text('<?=getMessage("SETTED_KEYS_DETAIL")?>');
			}else{
				trItems.show();
				_this.text('<?=getMessage("SETTED_KEYS_CUT")?>');
			}

			event.preventDefault();
		});
	});

</script>
<?
$obJSPopup->StartButtons();
$obJSPopup->ShowStandardButtons(array('close'));
$obJSPopup->EndButtons();
?>