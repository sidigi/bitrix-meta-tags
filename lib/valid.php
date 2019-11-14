<?php
namespace Shantilab\MetaTags;
IncludeModuleLangFile(__FILE__);

class Valid{
	public static function checkRequestExpression($request){

		$request = explode("=",$request);

		if (is_array($request) && count($request) == 2 && $_REQUEST[$request[0]] == $request[1]){
			return true;
		}elseif(is_array($request) && count($request) == 1  && isset($_REQUEST[$request[0]])){
			return true;
		}

		return false;
	}

	public function getVarValFromText($variable){
		$pattern = "/^[$]{1}[A-za-z]{1}[a-zA-Z0-9]+/";
		if (defined("BX_UTF")){
			$pattern .= 'u';
		}
		preg_match($pattern, $variable, $matches);
		if ($matches){
			$var = substr(current($matches),1);
			global $$var;
			$arTmp = (explode("]",str_replace(current($matches),"",$variable)));
			$arValue = $$var;
			foreach($arTmp as $index => $val){
				$arReplace = array("\"","\'","]","["," ");
				$val = str_replace($arReplace,"",$val);
				if ($val){
					$arValue = $arValue[$val];
				}
			}
			if ($arValue)
				$variable = $arValue;
		}

		if ($arValue)
			return $variable;
		else
			return '';
	}
}