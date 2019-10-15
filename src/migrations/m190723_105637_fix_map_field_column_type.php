<?php

namespace ether\simplemap\migrations;

use Craft;
use craft\db\Migration;
use ether\simplemap\fields\MapField;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

/**
 * m190723_105637_fix_map_field_column_type migration.
 */
class m190723_105637_fix_map_field_column_type extends Migration
{

	/**
	 * @inheritdoc
	 * @throws NotSupportedException
	 * @throws ErrorException
	 * @throws Exception
	 * @throws ServerErrorHttpException
	 */
	public function safeUp ()
	{
		$pc = Craft::$app->getProjectConfig();

		$fields               = $pc->get('fields', true) ?? [];
		$matrixBlockTypes     = $pc->get('matrixBlockTypes', true) ?? [];
		$superTableBlockTypes = $pc->get('superTableBlockTypes', true) ?? [];
		$updates              = [];

		foreach ($fields as $f => $field)
			if ($field['type'] === MapField::class && $field['contentColumnType'] !== 'text')
				$updates["fields.$f.contentColumnType"] = 'text';

		foreach ($matrixBlockTypes as $b => $blockType)
			if (array_key_exists('fields', $blockType) && is_array($blockType['fields']))
				foreach ($blockType['fields'] as $f => $field)
					if ($field['type'] === MapField::class && $field['contentColumnType'] !== 'text')
						$updates["matrixBlockTypes.$b.fields.$f.contentColumnType"] = 'text';

		foreach ($superTableBlockTypes as $b => $blockType)
			if (array_key_exists('fields', $blockType) && is_array($blockType['fields']))
				foreach ($blockType['fields'] as $f => $field)
					if ($field['type'] === MapField::class && $field['contentColumnType'] !== 'text')
						$updates["superTableBlockTypes.$b.fields.$f.contentColumnType"] = 'text';

		foreach ($updates as $path => $value)
			$pc->set($path, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown ()
	{
		echo "m190723_105637_fix_map_field_column_type cannot be reverted.\n";

		return false;
	}

}
