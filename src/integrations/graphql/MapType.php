<?php
/**
 * Maps for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\simplemap\integrations\graphql;

use craft\gql\GqlEntityRegistry;
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
		return 'Map';
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

}
