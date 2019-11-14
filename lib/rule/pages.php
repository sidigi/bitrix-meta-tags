<?php
namespace Shantilab\MetaTags\Rule;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class PagesTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'shantilab_metatags_rules_pages';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('RULES_PAGES_ENTITY_ID_FIELD'),
			),
			'RULE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('RULES_PAGES_ENTITY_RULE_ID_FIELD'),
			),
			'PAGE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validatePage'),
				'title' => Loc::getMessage('RULES_PAGES_ENTITY_PAGE_FIELD'),
			),
			'SHOW_ON_PAGE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('RULES_PAGES_ENTITY_SHOW_ON_PAGE_FIELD'),
			),
		);
	}
	public static function validatePage()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
}