<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class CIblockKeysAuotiSet extends CBitrixComponent

{
	private $_componentModes = array(0 =>"auto", 1 => "request", 2 => "complex");
	private $_curMode;
	private $_elementId;
	private $_sectionId;
	private $_sectionCode;
	private $_elementCode;
	
	public function onPrepareComponentParams($arParams)
	{
		$result = array(
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => isset($arParams["CACHE_TIME"]) ?$arParams["CACHE_TIME"]: 36000000,
			"SECTION_ID" => intval($arParams["SECTION_ID"]),
			"ELEMENT_ID" => intval($arParams["ELEMENT_ID"]),
			"SECTION_CODE" => trim(strip_tags($arParams["SECTION_CODE"])),
			"ELEMENT_CODE" => trim(strip_tags($arParams["ELEMENT_CODE"])),
			"COMPLEX_COMPONENT_PATH" => trim(strip_tags($arParams["COMPLEX_COMPONENT_PATH"])),
			"COMPLEX_SECTION_PATH" => trim(strip_tags($arParams["COMPLEX_SECTION_PATH"])),
			"COMPLEX_ELEMENT_PATH" => trim(strip_tags($arParams["COMPLEX_ELEMENT_PATH"])),
			"IBLOCK_ID" => intval($arParams["IBLOCK_ID"]),
			"IBLOCK_TYPE" => trim(strip_tags($arParams["IBLOCK_TYPE"])),
			"COMPONENT_MODE" => intval($arParams["COMPONENT_MODE"]),
		);
		return $result;
	}
	
	public function getSectionCode(){
		return $this->_sectionCode;
	}
	
	public function getSectionId(){
		return $this->_sectionId;
	}
	
	public function getElementId(){
		return $this->_elementId;
	}
	
	public function getElementCode(){
		return $this->_elementCode;
	}
	
	public function getCurrentMode(){
		return $this->_componentModes[$this->_curMode];
	}
	
	public function InitCurrentMode(){
		$this->_curMode = $this->arParams['COMPONENT_MODE'];
	}

	public function setKeys(){
		$this->keysInstall();
	}
	
	public function setElementKeys(){
		$elementId = $this->getElementId();

		if (!$elementId || !$this->arParams['IBLOCK_ID']){
			return false;
		}
		
		$arSelect = Array("ID","IBLOCK_ID", "NAME");
		$arFilter = Array("IBLOCK_ID"=>$this->arParams['IBLOCK_ID'], "ID" =>$elementId);
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
		if($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();
			$this->arResult['META_KEYS']['ELEMENT_NAME'] = array("NAME" => getMessage("ELEMENT_NAME"), "VALUE" => $arFields['NAME'],"CODE" => "ELEMENT_NAME");
			$arFields['PROPERTIES'] = $ob->GetProperties();

			foreach ($arFields['PROPERTIES'] as  $property){

				if (!$property['CODE']){
					$suffix = $property['ID'];
				}else{
					$suffix = $property['CODE'];
				}

				//Html
				if ($property['PROPERTY_TYPE'] == 'S' && $property['USER_TYPE'] == 'HTML'){
					if ($property["MULTIPLE"] == "Y"){
						$arpropHtml = array();
						foreach($property["VALUE"] as $htmlProp){
							if ($htmlProp["TEXT"]){
								$arpropHtml[] = $htmlProp["TEXT"];
							}
						}
						$property['VALUE'] = implode(', ', $arpropHtml);
					}else{
						$property['VALUE'] = $property["VALUE"]["TEXT"];
					}
					
				}
			
				if ($property['PROPERTY_TYPE'] == 'S' || $property['PROPERTY_TYPE'] == 'L' || $property['PROPERTY_TYPE'] == 'N'){
					if ($property['VALUE'] && is_array($property['VALUE'])){
						$this->arResult['META_KEYS']['ELEMENT_'.$suffix] = array("NAME" => getMessage("PROPERTY_ELEMENT").$suffix,"VALUE" =>implode(', ', $property['VALUE']),"CODE" => 'ELEMENT_'.$suffix);
					}elseif($property['VALUE']){
						$this->arResult['META_KEYS']['ELEMENT_'.$suffix] = array("NAME" => getMessage("PROPERTY_ELEMENT").$suffix,"VALUE" =>$property['VALUE'],"CODE" => 'ELEMENT_'.$suffix);
					}
				}
			}
		}
	}
	
	public function setSectionIdByElementID(){
		$elementId = $this->getElementId();
		
		if (!$elementId){
			return false;
		}
		
		CModule::IncludeModule('iblock');
		
		$arSelect = Array("ID", "IBLOCK_SECTION_ID");
		$arFilter = Array("IBLOCK_ID"=>$this->arParams['IBLOCK_ID'], "ID" => $elementId);
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
		if($arFields = $res->fetch())
		{
			if ($arFields['IBLOCK_SECTION_ID'])
				$this->_sectionId = $arFields['IBLOCK_SECTION_ID'];
		}
	}
	
	public function setSectionKeys(){
		$sectionId = $this->getSectionId();
		$elementId = $this->getElementId();
		
		if ($elementId && !$sectionId){
			$this->setSectionIdByElementID();
			$sectionId = $this->getSectionId();
		}
		
		if (!$sectionId || !$this->arParams['IBLOCK_ID']){
			return false;
		}

		$arFilter = Array('IBLOCK_ID' => $this->arParams['IBLOCK_ID'], 'ACTIVE'=>'Y',"ID" =>$sectionId);
		$db_list = CIBlockSection::GetList(Array(), $arFilter, false);
		if($ar_result = $db_list->fetch())
		{
			$arSection = $ar_result;
		}
		
		if ($arSection){
			$arFilter = Array('IBLOCK_ID' => $this->arParams['IBLOCK_ID'], 'GLOBAL_ACTIVE'=>'Y',"<=LEFT_BORDER" =>$arSection['LEFT_MARGIN'],">=RIGHT_BORDER" =>$arSection['RIGHT_MARGIN']);
			$db_list = CIBlockSection::GetList(Array("DEPTH_LEVEL" => "ASC"), $arFilter, false, array('UF_*'));
			while($ar_result = $db_list->fetch())
			{
				
				$this->arResult['META_KEYS']['SECTION_NAME'] = array("NAME" => getMessage("SECTION_CUR_NAME"), "VALUE" => $ar_result['NAME'],"CODE" => "SECTION_NAME");
				$this->arResult['META_KEYS']['SECTION_'.$ar_result['DEPTH_LEVEL'].'_NAME'] = array("NAME" => getMessage("SECTION_NAME").' '.$ar_result['DEPTH_LEVEL'], "VALUE" => $ar_result['NAME'],"CODE" => 'SECTION_'.$ar_result['DEPTH_LEVEL'].'_NAME');
				
				foreach($ar_result as $key => $value){
					$posUF = strpos($key, "UF");
					
					if ($posUF !== false) {//UF in key
	
						$depthSuffix = $ar_result['DEPTH_LEVEL'].'_';
						if ($value && is_array($value)){
							$value = implode(',',$value);
						}

						if ($sectionId == $ar_result['ID']){
							$this->arResult['META_KEYS']['SECTION_'.$key] = array("NAME" => getMessage("SECTION_CUR_PROPERTY").$key, "VALUE" => $value,"CODE" => 'SECTION_'.$key);
						}

						$this->arResult['META_KEYS']['SECTION_'.$depthSuffix.$key] = array("NAME" => getMessage("SECTION_PROPERTY").$depthSuffix.$key, "VALUE" => $value,"CODE" => 'SECTION_'.$depthSuffix.$key);
					}
				}
			}
		}
	}
	
	public function keysInstall(){
		if(CModule::IncludeModuleEx('shantilab.metatags')){
			$elementId = $this->getElementId();
			if ($elementId){
				$prefix = 'D_';
			}else{
				$prefix = 'S_';
			}

			$arKeys = array();
			foreach($this->arResult['META_KEYS'] as $key=>$nameKey){
			   $arKeys[] = array("CODE"=>$prefix.$nameKey["CODE"],"VALUE"=>$nameKey["VALUE"],"NAME" => $nameKey["NAME"],"SET_INFO"=>array("PATH" => __FILE__),"DESCRIPTION" => getMessage('KEY_DESCRIPTION'));
			}
			
			if ($arKeys){
				Shantilab\MetaTags\Key::addGroup($arKeys);
			}
		}
	}
	
	public function setIdsInParams(){
		$sectionId = $this->getSectionId();
		$elementId = $this->getElementId();
		
		$this->arParams['SECTION_ID'] = $sectionId;
		$this->arParams['ELEMENT_ID'] = $elementId;
	}
	
	public function getParamsFromComplexComponent(){
		if (!$this->arParams['COMPLEX_COMPONENT_PATH'] ||
			(!$this->arParams['COMPLEX_SECTION_PATH'] &&
			!$this->arParams['COMPLEX_ELEMENT_PATH'])
		){
			return false;
		}
		
		$engine = new CComponentEngine($this);
		$engine->addGreedyPart("#SECTION_CODE_PATH#");
		$engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));
		$arUrlTemplates = array(
			"SECTION_ID" => $this->arParams['COMPLEX_SECTION_PATH'], 
			"ELEMENT_ID" => $this->arParams['COMPLEX_ELEMENT_PATH'],
		);

		$engine->guessComponentPath(
			$this->arParams['COMPLEX_COMPONENT_PATH'],
			$arUrlTemplates,
			$arVariables
		);

		if (!$arVariables){
			return false;
		}

		if ($arVariables['SECTION_ID']){
			$section = $arVariables['SECTION_ID'];
		}
		if ($arVariables['SECTION_CODE']){
			$section = $arVariables['SECTION_CODE'];
		}
		
		if($section){
			$this->setSectionId($section);
		}
		
		if ($arVariables['ELEMENT_ID']){
			$element = $arVariables['ELEMENT_ID'];
		}
		if ($arVariables['ELEMENT_CODE']){
			$element = $arVariables['ELEMENT_CODE'];
		}
		
		if($element){
			$this->setElementId($element);
		}
		
		return true;
	}

	public function setSectionId($sectionVal){
		if (!$sectionVal){
			return false;
		}

		if (preg_match('/^(0)$|^([1-9][0-9]*)$/u', $sectionVal) && intval($sectionVal)){
			$this->_sectionId = intval($sectionVal);
		}else{
			$this->_sectionCode = $sectionVal;
			$this->getSectionIdByCode();
		}
	}
	
	public function setElementId($elementVal){
		if (!$elementVal){
			return false;
		}

		if (preg_match('/^(0)$|^([1-9][0-9]*)$/u', $elementVal) && intval($elementVal)){
			$this->_elementId = intval($elementVal);
		}else{
			$this->_elementCode = $elementVal;
			$this->getElementIdByCode();
		}
	}
	
	public function getSectionIdByCode(){
		$sectionCode = $this->getSectionCode();

		if (!$sectionCode || !$this->arParams['IBLOCK_ID']){
			return false;
		}
		
		CModule::IncludeModule('iblock');
		
		$arFilter = Array('IBLOCK_ID'=>$this->arParams['IBLOCK_ID'], 'CODE'=>$sectionCode);
		$db_list = CIBlockSection::GetList(Array(), $arFilter, false);
		if($ar_result = $db_list->fetch())
		{
			$this->_sectionId = intval($ar_result['ID']);
		}
	}
	
	public function getElementIdByCode(){
		$elementCode = $this->getElementCode();

		if (!$elementCode || !$this->arParams['IBLOCK_ID']){
			return false;
		}
		
		CModule::IncludeModule('iblock');
		
		$arSelect = Array("ID");
		$arFilter = Array("IBLOCK_ID" => $this->arParams['IBLOCK_ID'], "CODE"=>$elementCode);
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
		if($arFields = $res->fetch())
		{
			$this->_elementId = intval($arFields['ID']);
		}

	}
	
	public function getParamsFromRequest(){
		$this->getSectionParam();
		$this->getElementParam();
	}
	
	public function getSectionParam(){
		if ($this->arParams['SECTION_CODE'] && !$this->arParams['SECTION_ID']){
			$this->setSectionId($this->arParams['SECTION_CODE']);
			return;
		}
		
		$this->_sectionId = intval($this->arParams['SECTION_ID']);
		return;
	}
	
	public function getElementParam(){
		if ($this->arParams['ELEMENT_CODE'] && !$this->arParams['ELEMENT_ID']){
			$this->setElementId($this->arParams['ELEMENT_CODE']);
			return;
		}
		
		$this->_elementId = intval($this->arParams['ELEMENT_ID']);
		return;
	}
	
	public function executeComponent()
	{
		return parent::executeComponent();
	}
}