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

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

$sTableID = 'tbl_shantilab_metatags_rules_list';

$oSort = new CAdminSorting($sTableID, "TIMESTAMP_X", "desc");
$arOrder = (strtoupper($by) === "ID" ? array($by => $order) : array($by => $order, "TIMESTAMP_X" => "desc"));
$lAdmin = new CAdminList($sTableID, $oSort);

function cmpPosition($a, $b)
{
	if ($a["position"] == $b["position"]) {
		return 0;
	}
	return ($a["position"] < $b["position"]) ? -1 : 1;
}

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;

	return count($lAdmin->arFilterErrors) == 0;
}

$FilterArr = Array(
	"find_id",
	"find_active",
	"find_timestamp_1","find_timestamp_2",
	"find_created_by",
	"find_modified_by",
	"find_date_create_1","find_date_create_2",
	"find_active_from_1","find_active_from_2",
	"find_active_to_1","find_active_to_2",
	"find_sort",
	"find_name",
	"find_c_site",
	"find_c_template",
	"find_c_request",
	"find_c_php",
	"find_show_on",
	"find_show_off",
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter())
{
	$arFilter = Array(
		"ID"		    => $find_id,
		"ACTIVE"	    => $find_active,
		"TIMESTAMP_X"	=> "",
		"CREATED_BY"	=> $find_created_by,
		"MODIFIED_BY"	=> $find_modified_by,
		"DATE_CREATE"	=> "",
		"ACTIVE_FROM"	=> "",
		"ACTIVE_TO"	    => "",
		"SORT"  	    => $find_sort,
		"NAME"  	    => $find_name,
		"CODE"  	    => $find_code,
		"VALUE"  	    => $find_value,
		"C_SITE"  	    => $find_c_site,
		"C_TEMPLATE"    => $find_c_template,
		"C_REQUEST"     => $find_c_request,
		"C_PHP"         => $find_c_php,
	);

	if(!empty($find_timestamp_1))
		$arFilter[">=TIMESTAMP_X"] = ConvertTimeStamp(AddTime(MakeTimeStamp($find_timestamp_1), 1, "D"), "FULL");
	if(!empty($find_timestamp_2))
		$arFilter["<=TIMESTAMP_X"] = ConvertTimeStamp(AddTime(MakeTimeStamp($find_timestamp_2), 1, "D"), "FULL");
	if(!empty($find_date_create_1))
		$arFilter[">=DATE_CREATE"] = ConvertTimeStamp(AddTime(MakeTimeStamp($find_date_create_1), 1, "D"), "FULL");
	if(!empty($find_date_create_2))
		$arFilter["<=DATE_CREATE"] = ConvertTimeStamp(AddTime(MakeTimeStamp($find_date_create_2), 1, "D"), "FULL");
	if(!empty($find_active_from_1))
		$arFilter[">=ACTIVE_FROM"] = ConvertTimeStamp(AddTime(MakeTimeStamp($find_active_from_1), 1, "D"), "FULL");
	if(!empty($find_active_from_2))
		$arFilter["<=ACTIVE_FROM"] = ConvertTimeStamp(AddTime(MakeTimeStamp($find_active_from_2), 1, "D"), "FULL");
	if(!empty($find_active_to_1))
		$arFilter[">=ACTIVE_TO"] = ConvertTimeStamp(AddTime(MakeTimeStamp($find_active_to_1), 1, "D"), "FULL");
	if(!empty($find_active_to_2))
		$arFilter["<=ACTIVE_TO"] = ConvertTimeStamp(AddTime(MakeTimeStamp($find_active_to_2), 1, "D"), "FULL");
}
$arFilter = array_filter($arFilter);
$listParams = array();
$listParams["order"] = $arOrder;
if ($arFilter){
	$listParams["filter"] = $arFilter;
}

if($lAdmin->EditAction())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		$ID = IntVal($ID);
		$rsData = Shantilab\MetaTags\Rule\DB::getById($ID);
		if($arData = $rsData->Fetch())
		{
			foreach($arFields as $key=>$value){

				if (in_array($key,array("ACTIVE_TO","ACTIVE_FROM")) && $value){
					$arData[$key]= new \Bitrix\Main\Type\DateTime(ConvertTimeStamp(AddTime(MakeTimeStamp($value), 1, "D"), "FULL"));
				}else{
					$arData[$key] = $value;
				}
			}
			$res = Shantilab\MetaTags\Rule\DB::update($ID, $arData);
			if(!$res->isSuccess())
			{
				$lAdmin->AddGroupError(GetMessage("RULE_SAVE_ERROR")." ".implode("<br>", $res->getErrorMessages()), $ID);
			}
		}
		else
		{
			$lAdmin->AddGroupError(GetMessage("RULE_SAVE_ERROR")." ".GetMessage("RULE_DOES_NOT_EXIST"), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if(($arID = $lAdmin->GroupAction()))
{
	if($request->get('action_target') =='selected')
	{
		$rsData = Shantilab\MetaTags\Rule\DB::getList($listParams);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

		$ID = IntVal($ID);

		switch($request->get('action_button'))
		{
			case "delete":
				@set_time_limit(0);
				$res = Shantilab\MetaTags\Rule\DB::delete($ID);
				if(!$res->isSuccess())
				{
					$lAdmin->AddGroupError(GetMessage("RULE_DEL_ERROR"), $ID);
					$lAdmin->AddGroupError($res->getErrorMessages(), $ID);
				}
				break;

			// активация/деактивация
			case "activate":
			case "deactivate":
				$rsData = Shantilab\MetaTags\Rule\DB::getById($ID);
				if($rsData->Fetch())
				{
					$arFields["ACTIVE"]=  ($request->get('action_button') == "activate" ? "Y" : "N");
					$res = Shantilab\MetaTags\Rule\DB::update($ID, $arFields);
					if(!$res->isSuccess())
						$lAdmin->AddGroupError(GetMessage("RULE_SAVE_ERROR")." ".implode("<br>", $res->getErrorMessages()), $ID);
				}
				else
					$lAdmin->AddGroupError(GetMessage("RULE_SAVE_ERROR")." ".GetMessage("RULE_DOES_NOT_EXIST"), $ID);
				break;
		}

	}
}

$rsData = Shantilab\MetaTags\Rule\DB::getList($listParams);

$rsData = new CAdminResult($rsData, $sTableID);

$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(getMessage("RULES_NAV_TEXT")));

$arUsersCache = array();

$columns = Shantilab\MetaTags\Rule\RulesTable::getMap();
$defaultHeaders = array("ID" => 100,"NAME"=>200,"ACTIVE"=>300,"SORT"=>400);
$headers = array();
foreach($columns as $key=>$head){
	if ($head["reference"])
		continue;

	$headers[] = array(
		"id"       => $key,
		"content"  => $head["title"],
		"sort"     => $key,
		"default"  => ($defaultHeaders[$key]) ? true : false,
		"position" => ($defaultHeaders[$key]) ? $defaultHeaders[$key] : 1000,
	);
}

$headers[] = array(
	"id"       => "SHOW_ON",
	"content"  => getMessage("SHOW_ON_FIELD"),
	"default"  => false,
);

$headers[] = array(
	"id"       => "SHOW_OFF",
	"content"  => getMessage("SHOW_OFF_FIELD"),
	"default"  => false,
);


if ($headers){
	usort($headers, "cmpPosition");
	$lAdmin->AddHeaders($headers);
}

$siteList = Shantilab\MetaTags\Data::getSiteList();
$siteTemplateList = Shantilab\MetaTags\Data::getTemplateList();

while($arRes = $rsData->NavNext(true, "f_")):
	/*pages*/
	$rs = Shantilab\MetaTags\Rule\PagesTable::getList(array(
		"filter" => array("RULE_ID" => $f_ID)
	));
	$f_SHOW_ON = '';
	$f_SHOW_OFF = '';
	while($arTmp = $rs->fetch()){
		if ($arTmp["SHOW_ON_PAGE"] == "Y"){
			$f_SHOW_ON .= $arTmp["PAGE"]."<br/>";
		}else{
			$f_SHOW_OFF .= $arTmp["PAGE"]."<br/>";
		}
	}
	/**/
	$arRes["C_SITE"] = Shantilab\MetaTags\Data::makeArrayFromHash($arRes["C_SITE"]);
	$arRes["C_TEMPLATE"] = Shantilab\MetaTags\Data::makeArrayFromHash($arRes["C_TEMPLATE"]);

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("TIMESTAMP_X", $f_TIMESTAMP_X);
	$row->AddViewField("DATE_CREATE",$f_DATE_CREATE);

	$row->AddViewField("SHOW_ON",$f_SHOW_ON);
	$row->AddViewField("SHOW_OFF",$f_SHOW_OFF);

	$row->AddCalendarField("ACTIVE_FROM");
	$row->AddCalendarField("ACTIVE_TO");

	$row->AddInputField("NAME", array("size"=>20));
	$row->AddViewField("NAME", '<a href="'.Shantilab\MetaTags\Data::getPageName('edit_rule').'?ID='.$f_ID.'&lang='.LANG.'">'.$f_NAME.'</a>');

	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("C_ALL_KEYS");
	$row->AddInputField("SORT", array("size"=>20));

	if(!array_key_exists($f_MODIFIED_BY, $arUsersCache))
	{
		$rsUser = CUser::GetByID($f_MODIFIED_BY);
		$arUsersCache[$f_MODIFIED_BY] = $rsUser->Fetch();
	}
	if($arUser = $arUsersCache[$f_MODIFIED_BY])
		$row->AddViewField("MODIFIED_BY", '[<a href="user_edit.php?lang='.LANG.'&ID='.$f_MODIFIED_BY.'" title="'.GetMessage("USER_INFO").'">'.$f_MODIFIED_BY."</a>]&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"]);

	if(!array_key_exists($f_CREATED_BY, $arUsersCache))
	{
		$rsUser = CUser::GetByID($f_CREATED_BY);
		$arUsersCache[$f_CREATED_BY] = $rsUser->Fetch();
	}
	if($arUser = $arUsersCache[$f_CREATED_BY])
		$row->AddViewField("CREATED_BY", '[<a href="user_edit.php?lang='.LANG.'&ID='.$f_CREATED_BY.'" title="'.GetMessage("USER_INFO").'">'.$f_CREATED_BY."</a>]&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"]);

	$row->AddInputField("C_REQUEST", array("size"=>20));
	$row->AddInputField("C_PHP", array("size"=>20));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=> GetMessage("RULE_EDIT_TITLE"),
		"ACTION"=>$lAdmin->ActionRedirect(Shantilab\MetaTags\Data::getPageName('edit_rule')."?ID=".$f_ID.'&lang='.LANG)
	);

	if($f_ACTIVE == "Y")
	{
		$arActions[] = array(
			"ICON" => "",
			"TEXT" => GetMessage("RULE_DEACTIVE_TITLE"),
			"ACTION" => $lAdmin->ActionDoGroup($f_ID, "deactivate"),
			"ONCLICK" => "",
		);
	}
	else
	{
		$arActions[] = array(
			"ICON" => "",
			"TEXT" => GetMessage("RULE_ACTIVE_TITLE"),
			"ACTION" => $lAdmin->ActionDoGroup($f_ID, "activate"),
			"ONCLICK" => "",
		);
	}

	$arActions[] = array(
		"ICON" => "copy",
		"TEXT" => GetMessage("RULE_COPY_TITLE"),
		"ACTION" => $lAdmin->ActionRedirect(Shantilab\MetaTags\Data::getPageName('edit_rule')."?ID=".$f_ID.'&action=copy&lang='.LANG)
	);

	$arActions[] = array("SEPARATOR"=>true);

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage("RULE_DEL_TITLE"),
		"ACTION"=>"if(confirm('".GetMessage('DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
	);


	if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
		unset($arActions[count($arActions)-1]);

	$row->AddActions($arActions);

endwhile;

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
));

$aContext = array(
	array(
		"TEXT"=>GetMessage("RULE_ADD_TITLE"),
		"LINK"=> Shantilab\MetaTags\Data::getPageName('edit_rule')."?lang=".LANG,
		"TITLE"=>GetMessage("RULE_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("RULES_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>
<?

if ($moduleMode == 2){
	echo BeginNote();
	echo GetMessage("DEMO_INFO");
	echo EndNote();
}

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		$columns["ACTIVE"]["title"],
		$columns["TIMESTAMP_X"]["title"],
		$columns["CREATED_BY"]["title"],
		$columns["MODIFIED_BY"]["title"],
		$columns["DATE_CREATE"]["title"],
		$columns["ACTIVE_FROM"]["title"],
		$columns["ACTIVE_TO"]["title"],
		$columns["SORT"]["title"],
		$columns["NAME"]["title"],
		$columns["C_SITE"]["title"],
		$columns["C_TEMPLATE"]["title"],
		$columns["C_REQUEST"]["title"],
		$columns["C_PHP"]["title"],
	)
);
?>
	<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
		<?$oFilter->Begin();?>
		<tr>
			<td><?="ID"?>:</td>
			<td>
				<input type="text" name="find_id" size="47" value="<?echo htmlspecialchars($find_id)?>">
			</td>
		</tr>
		<tr>
			<td><?=$columns["ACTIVE"]["title"]?>:</td>
			<td>
				<?
				$arr = array(
					"reference" => array(
						GetMessage("RULE_YES"),
						GetMessage("RULE_NO"),
					),
					"reference_id" => array(
						"Y",
						"N",
					)
				);
				echo SelectBoxFromArray("find_active", $arr, $find_active, GetMessage("RULE_ALL"), "");
				?>
			</td>
		</tr>
		<tr>
			<td><?=$columns["TIMESTAMP_X"]["title"]?>:</td>
			<td>
				<?echo CalendarPeriod("find_timestamp_1", htmlspecialcharsbx($find_timestamp_1), "find_timestamp_2", htmlspecialcharsbx($find_timestamp_2), "find_form", "Y")?>
			</td>
		</tr>
		<tr>
			<td><?=$columns["CREATED_BY"]["title"]?>:</td>
			<td>
				<?echo FindUserID(
					"find_created_by",
					$find_created_by,
					"",
					"find_form",
					"5",
					"",
					" ... ",
					"",
					""
				);?>
			</td>
		</tr>
		<tr>
			<td><?=$columns["MODIFIED_BY"]["title"]?>:</td>
			<td>
				<?echo FindUserID(
					"find_modified_by",
					$find_modified_by,
					"",
					"find_form",
					"5",
					"",
					" ... ",
					"",
					""
				);?>
			</td>
		</tr>
		<tr>
			<td><?=$columns["DATE_CREATE"]["title"]?>:</td>
			<td>
				<?echo CalendarPeriod("find_date_create_1", htmlspecialcharsbx($find_date_create_1), "find_date_create_2", htmlspecialcharsbx($find_date_create_2), "find_form", "Y")?>
			</td>
		</tr>
		<tr>
			<td><?= $columns["ACTIVE_FROM"]["title"]?>:</td>
			<td><?= CalendarPeriod("find_active_from_1", htmlspecialcharsex($find_active_from_1), "find_active_from_2", htmlspecialcharsex($find_active_from_2), "find_form")?></td>
		</tr>
		<tr>
			<td><?= $columns["ACTIVE_TO"]["title"]?>:</td>
			<td><?= CalendarPeriod("find_active_to_1", htmlspecialcharsex($find_active_to_1), "find_active_to_2", htmlspecialcharsex($find_active_to_2), "find_form")?></td>
		</tr>
		<tr>
			<td><?=$columns["SORT"]["title"]?>:</td>
			<td>
				<input type="text" name="find_sort" size="47" value="<?echo htmlspecialcharsbx($find_sort)?>">
			</td>
		</tr>
		<tr>
			<td><?=$columns["NAME"]["title"]?>:</td>
			<td>
				<input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>">
			</td>
		</tr>
		<tr>
			<td><?=$columns["C_SITE"]["title"]?>:</td>
			<td>
				<?
				$arr = array();
				foreach($siteList as $arSite){
					$arr["reference"][] = "[".$arSite["LID"]."] ".$arSite["NAME"];
					$arr["reference_id"][] = "#".$arSite["LID"]."#";
				}
				?>
				<?=SelectBoxFromArray("find_c_site", $arr, $find_c_site, GetMessage("RULE_ALL"))?>
			</td>
		</tr>
		<tr>
			<td><?=$columns["C_TEMPLATE"]["title"]?>:</td>
			<td>
				<?
				$arr = array();
				foreach($siteTemplateList as $arTemplate)
				{
					$arr["reference"][] = "[".$arTemplate["ID"]."] ".$arTemplate["NAME"];
					$arr["reference_id"][] = "#".$arTemplate["ID"]."#";
				}
				?>
				<?=SelectBoxFromArray("find_c_template", $arr, $find_c_template, GetMessage("RULE_ALL"), "")?>
			</td>
		</tr>
		<tr>
			<td><?=$columns["C_REQUEST"]["title"]?>:</td>
			<td>
				<input type="text" name="find__c_request" size="47" value="<?echo htmlspecialcharsbx($find_c_request)?>">
			</td>
		</tr>
		<tr>
			<td><?=$columns["C_PHP"]["title"]?>:</td>
			<td>
				<input type="text" name="find_c_php" size="47" value="<?echo htmlspecialcharsbx($find_c_php)?>">
			</td>
		</tr>
		<?
		$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
		$oFilter->End();
		?>
	</form>
<?
$lAdmin->DisplayList();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>