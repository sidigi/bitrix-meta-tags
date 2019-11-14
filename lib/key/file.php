<?php
namespace Shantilab\MetaTags\Key;
IncludeModuleLangFile(__FILE__);

class File extends Base{
	protected static $pageName = '.page_keys.php';
	protected static $dirName = '.sect_keys.php';

	protected function getDirName(){
		return self::$dirName;
	}

	protected function getPageName(){
		return self::$pageName;
	}

	public function getForPage(){
		$keysFromFile = array();
		$io = \CBXVirtualIo::GetInstance();

		$sFilePath = self::getCurDir();

		$sFileName = self::getPageName();

		$filePath = $_SERVER['DOCUMENT_ROOT'].$sFilePath.$sFileName;

		$bFileFound = $io->FileExists($filePath);

		if ($bFileFound){
			$keysFromFile = array_merge($keysFromFile,self::getfromFile($filePath));
		}

		return $keysFromFile;
	}

	public function getForDir(){
		$keysFromFile = array();
		$sFilePath = self::getCurDir();
		$sFileName = self::getDirName();

		$io = \CBXVirtualIo::GetInstance();

		$dirPath = explode('/',$sFilePath);
		trimArr($dirPath);
		$dirPath = array_values($dirPath);
		array_unshift($dirPath,"/");

		foreach($dirPath as $key=>$path){
			if ($key != 0)
				$path = "/$path/";

			$bFileFound = $io->FileExists($_SERVER['DOCUMENT_ROOT'].$path.$sFileName);

			if ($bFileFound){
				$filePath =$_SERVER['DOCUMENT_ROOT'].$path.$sFileName;
				$keysFromFile = array_merge($keysFromFile,self::getfromFile($filePath));
			}
		}

		return $keysFromFile;
	}

	protected function getfromFile($filePath){
		$keysFromFile = array();

		include($filePath);

		\Shantilab\MetaTags\Key::prepare($keys);

		if ($keys && is_array($keys)){
			foreach($keys as $key=>$value){
				$keys[$key]["SET_INFO"]["PATH"] = str_replace($_SERVER["DOCUMENT_ROOT"],'',$filePath);
			}

			$keysFromFile = array_merge($keysFromFile,$keys);
		}

		return $keysFromFile;
	}

	public function getCurDir(){
		global $APPLICATION;

		$sRealFilePath = $_SERVER["REAL_FILE_PATH"];

		if (strlen($sRealFilePath) > 0)
		{
			$slash_pos = strrpos($sRealFilePath, "/");
			$sFilePath = substr($sRealFilePath, 0, $slash_pos+1);
		}
		else
		{
			$sFilePath = $APPLICATION->GetCurPage(true);
		}

		return $sFilePath;
	}
}