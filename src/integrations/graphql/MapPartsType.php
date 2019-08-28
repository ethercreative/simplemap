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
 * Class MapPartsType
 *
 * @author  Ether Creative
 * @package ether\simplemap\integrations\graphql
 */
class MapPartsType
{

	public static function getName (): string
	{
		return 'MapParts';
	}

	public static function getFieldDefinitions (): array
	{
		return [
			'number'        => [
				'name'        => 'number',
				'type'        => Type::string(),
				'description' => 'The address name / number.',
			],
			'address' => [
				'name'        => 'address',
				'type'        => Type::string(),
				'description' => 'The street address.',
			],
			'city'          => [
				'name'        => 'city',
				'type'        => Type::string(),
				'description' => 'The city.',
			],
			'postcode'      => [
				'name'        => 'postcode',
				'type'        => Type::string(),
				'description' => 'The postal code.',
			],
			'county'        => [
				'name'        => 'county',
				'type'        => Type::string(),
				'description' => 'The county.',
			],
			'state'         => [
				'name'        => 'state',
				'type'        => Type::string(),
				'description' => 'The state.',
			],
			'country'       => [
				'name'        => 'country',
				'type'        => Type::string(),
				'description' => 'The country.',
			],
		];
	}

	public static function getType (): Type
	{
		if ($type = GqlEntityRegistry::getEntity(static::class))
			return $type;

		return GqlEntityRegistry::createEntity(
			static::class,
			new ObjectType(
				[
					'name'   => static::getName(),
					'fields' => static::class . '::getFieldDefinitions',
				]
			)
		);
	}

}
