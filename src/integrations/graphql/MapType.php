<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\integrations\graphql;

use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class MapType
 *
 * @author  Ether Creative
 * @package ether\simplemap\integrations\graphql
 */
class MapType
{

	public static function getName (): string
	{
		return 'Ether_Map';
	}

	public static function getFieldDefinitions (): array
	{
		return [
			'lat'      => [
				'name'        => 'lat',
				'type'        => Type::float(),
				'description' => 'The maps latitude.',
			],
			'lng'      => [
				'name'        => 'lng',
				'type'        => Type::float(),
				'description' => 'The maps longitude.',
			],
			'zoom'     => [
				'name'        => 'zoom',
				'type'        => Type::int(),
				'description' => 'The maps zoom level.',
			],
			'distance' => [
				'name'        => 'distance',
				'type'        => Type::float(),
				'description' => 'The distance to this location.',
			],
			'address'  => [
				'name'        => 'address',
				'type'        => Type::string(),
				'description' => 'The full address.',
			],
			'parts'    => [
				'name'        => 'parts',
				'type'        => MapPartsType::getType(),
				'description' => 'The maps address parts.',
			],
		];
	}

	public static function getInputDefinitions (): array
	{
		$fields = static::getFieldDefinitions();

		unset($fields['distance']);
		$fields['parts']['type'] = MapPartsType::getInputType();

		return $fields;
	}

	public static function getQueryInputDefinitions (): array
	{
		return [
			'coordinate' => [
				'name' => 'coordinate',
				'type' => static::getCoordsType(),
			],
			'location' => [
				'name' => 'location',
				'type' => Type::string(),
			],
			'country' => [
				'name' => 'country',
				'type' => Type::string(),
			],
			'radius' => [
				'name' => 'radius',
				'type' => Type::float(),
			],
			'unit' => [
				'name' => 'unit',
				'type' => static::getUnitType(),
			],
		];
	}

	public static function getType (): Type
	{
		if ($type = GqlEntityRegistry::getEntity(static::class))
			return $type;

		return GqlEntityRegistry::createEntity(
			static::class,
			new ObjectType([
				'name' => static::getName(),
				'fields' => static::class . '::getFieldDefinitions',
			])
		);
	}

	public static function getInputType (): InputType
	{
		$name = static::getName() . 'Input';

		if ($type = GqlEntityRegistry::getEntity($name))
			return $type;

		return GqlEntityRegistry::createEntity(
			$name,
			new InputObjectType([
				'name' => static::getName() . 'Input',
				'fields' => static::class . '::getInputDefinitions',
			])
		);
	}

	public static function getQueryType (): InputType
	{
		$name = static::getName() . 'Query';

		if ($type = GqlEntityRegistry::getEntity($name))
			return $type;

		return GqlEntityRegistry::createEntity(
			$name,
			new InputObjectType([
				'name' => static::getName() . 'Query',
				'fields' => static::class . '::getQueryInputDefinitions',
			])
		);
	}

	public static function getCoordsType (): InputType
	{
		$name = static::getName() . 'Coords';

		if ($type = GqlEntityRegistry::getEntity($name))
			return $type;

		return GqlEntityRegistry::createEntity(
			$name,
			new InputObjectType([
				'name' => static::getName() . 'Coords',
				'fields' => [
					'lat' => [
						'name' => 'lat',
						'type' => Type::nonNull(Type::float()),
					],
					'lng' => [
						'name' => 'lng',
						'type' => Type::nonNull(Type::float()),
					],
				],
			])
		);
	}

	public static function getUnitType (): EnumType
	{
		$name = static::getName() . 'Unit';

		if ($type = GqlEntityRegistry::getEntity($name))
			return $type;

		return GqlEntityRegistry::createEntity(
			$name,
			new EnumType([
				'name' => static::getName() . 'Unit',
				'values' => [
					'Miles' => 'mi',
					'Kilometres' => 'km',
				],
			])
		);
	}

}
