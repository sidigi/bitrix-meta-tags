<?php
namespace Shantilab\MetaTags\Rule;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class TemplatesTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'shantilab_metatags_rules_templates';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('RULES_TEMPLATES_ENTITY_ID_FIELD'),
			),
			'RULE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('RULES_TEMPLATES_ENTITY_RULE_ID_FIELD'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateCode'),
				'title' => Loc::getMessage('RULES_TEMPLATES_ENTITY_CODE_FIELD'),
			),
			'VALUE' => array(
				'data_type' => 'text',
				'required' => true,
				'title' => Loc::getMessage('RULES_TEMPLATES_ENTITY_VALUE_FIELD'),
			),
			'PAGEN' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validatePagen'),
				'title' => Loc::getMessage('RULES_TEMPLATES_ENTITY_PAGEN_FIELD'),
			),
			'PAGEN_ON' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('RULES_ENTITY_PAGEN_ON_FIELD'),
			),
		);
	}
	public static function validateCode()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validatePagen()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
}