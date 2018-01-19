<?php

namespace ether\simplemap\listeners;

use markhuot\CraftQL\Events\GetFieldSchema;
use ether\simplemap\services\MapService;

class GetCraftQLSchema
{
	/**
	 * Handle the request for the schema
	 *
	 * @param GetFieldSchema $event
	 *
	 * @return void
	 */
	function handle (GetFieldSchema $event)
	{
		$event->handled = true;

		$partsObject = $event->schema->createObjectType('SimpleMapDataParts');
		foreach (MapService::$parts as $part) {
			$partsObject->addStringField($part);
			$partsObject->addStringField($part . '_short');
		}

		$fieldObject = $event->schema->createObjectType('SimpleMapData');
		$fieldObject->addStringField('lat');
		$fieldObject->addStringField('lng');
		$fieldObject->addStringField('zoom');
		$fieldObject->addStringField('address');
		$fieldObject->addField('parts')->type($partsObject);

		$event->schema->addField($event->sender)
		              ->type($fieldObject);
	}
}
