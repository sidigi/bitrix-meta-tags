<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$moduleId = 'shantilab.metatags';
				
if (CModule::IncludeModuleEx($moduleId) != 1){
	return false;
}

$this->InitCurrentMode();
$curMode = $this->getCurrentMode();

if (!$curMode){
	return false;
}

if ($curMode == 'complex'){
	$this->getParamsFromComplexComponent();
}elseif($curMode == 'request'){
	$this->getParamsFromRequest();
}elseif($curMode == 'auto'){
	if (!$this->getParamsFromComplexComponent())
		$this->getParamsFromRequest();
}

$this->setIdsInParams();

if ($this->StartResultCache()){

	$this->setElementKeys();
	$this->setSectionKeys();

	$this->SetResultCacheKeys(array('META_KEYS'));
	
	$this->IncludeComponentTemplate();
}

if($arResult['META_KEYS'])
	$this->setKeys();
?>